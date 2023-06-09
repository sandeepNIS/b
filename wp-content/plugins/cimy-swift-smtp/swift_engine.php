<?php

#OVERRIDE WP_MAIL FUNCTION!!!!
if ( !function_exists('wp_mail') ) {
function wp_mail($to, $subject, $message, $headers = '', $attachments = array(), $echo_error = false) {
	global $st_smtp_config;

	// Compact the input, apply the filters, and extract them back out
	extract( apply_filters( 'wp_mail', compact( 'to', 'subject', 'message', 'headers', 'attachments' ) ) );

	if ( !is_array($attachments) )
		$attachments = explode( "\n", str_replace( "\r\n", "\n", $attachments ) );

	require_once 'vendor/autoload.php';
	$sender_email = "";
	$sender_name = "";
	$reply_to = ""; // mixed

	// Headers
	if ( empty( $headers ) ) {
		$headers = array();
	} else {
		if ( !is_array( $headers ) ) {
			// Explode the headers out, so this function can take both
			// string headers and an array of headers.
			$tempheaders = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
		} else {
			$tempheaders = $headers;
		}
		$headers = array();
		$cc = array();
		$bcc = array();

		// If it's actually got contents
		if ( !empty( $tempheaders ) ) {
			// Iterate through the raw headers
			foreach ( (array) $tempheaders as $header ) {
				if ( strpos($header, ':') === false ) {
					if ( false !== stripos( $header, 'boundary=' ) ) {
						$parts = preg_split('/boundary=/i', trim( $header ) );
						$boundary = trim( str_replace( array( "'", '"' ), '', $parts[1] ) );
					}
					continue;
				}
				// Explode them out
				list( $name, $content ) = explode( ':', trim( $header ), 2 );

				// Cleanup crew
				$name    = trim( $name    );
				$content = trim( $content );

				switch ( strtolower( $name ) ) {
					// Sender: is only one, is the one that actually send the email, mandatory in case of multiple From:
					// see: https://tools.ietf.org/html/rfc2822#section-3.6.2
					case 'sender':
						if ( strpos($content, '<' ) !== false ) {
							// So... making my life hard again?
							$sender_name = substr( $content, 0, strpos( $content, '<' ) - 1 );
							$sender_name = str_replace( '"', '', $sender_name );
							$sender_name = trim( $sender_name );

							$sender_email = substr( $content, strpos( $content, '<' ) + 1 );
							$sender_email = str_replace( '>', '', $sender_email );
							$sender_email = trim( $sender_email );
						} else {
							$sender_email = trim( $content );
						}
						break;
					// Mainly for legacy -- process a From: header if it's there
					case 'from':
						if ( strpos($content, '<' ) !== false ) {
							// So... making my life hard again?
							$from_name = substr( $content, 0, strpos( $content, '<' ) - 1 );
							$from_name = str_replace( '"', '', $from_name );
							$from_name = trim( $from_name );

							$from_email = substr( $content, strpos( $content, '<' ) + 1 );
							$from_email = str_replace( '>', '', $from_email );
							$from_email = trim( $from_email );
						} else {
							$from_email = trim( $content );
						}
						break;
					case 'reply-to':
						if ( strpos($content, '<' ) !== false ) {
							// So... making my life hard again?
							$reply_to_name = substr( $content, 0, strpos( $content, '<' ) - 1 );
							$reply_to_name = str_replace( '"', '', $reply_to_name );
							$reply_to_name = trim( $reply_to_name );

							$reply_to_email = substr( $content, strpos( $content, '<' ) + 1 );
							$reply_to_email = str_replace( '>', '', $reply_to_email );
							$reply_to_email = trim( $reply_to_email );
							$reply_to = array($reply_to_email => $reply_to_name);
						} else {
							$reply_to = trim( $content );
						}
						break;
					case 'content-type':
						if ( strpos( $content, ';' ) !== false ) {
							list( $type, $charset ) = explode( ';', $content );
							$content_type = trim( $type );
							if ( false !== stripos( $charset, 'charset=' ) ) {
								$charset = trim( str_replace( array( 'charset=', '"' ), '', $charset ) );
							} elseif ( false !== stripos( $charset, 'boundary=' ) ) {
								$boundary = trim( str_replace( array( 'BOUNDARY=', 'boundary=', '"' ), '', $charset ) );
								$charset = '';
							}
						} else {
							$content_type = trim( $content );
						}
						break;
					case 'cc':
						$cc = array_merge( (array) $cc, explode( ',', $content ) );
						break;
					case 'bcc':
						$bcc = array_merge( (array) $bcc, explode( ',', $content ) );
						break;
					default:
						// Add it to our grand headers array
						$headers[trim( $name )] = trim( $content );
						break;
				}
			}
		}
	}

	// overwriting if specified or necessary
	if ($st_smtp_config['overwrite_sender'] == "overwrite_always") {
		$from_name = $st_smtp_config['sender_name'];
		$from_email = $st_smtp_config['sender_mail'];
		// overwrite sender only in case of need
		if ((!empty($sender_name)) || (!empty($sender_email))) {
			$sender_name = $from_name;
			$sender_email = $from_email;
		}
	}

	// From email and name
	// If we don't have a name from the input headers
	if (empty($from_name)) {
		$from_name = 'WordPress';
	}

	/* If we don't have an email from the input headers default to wordpress@$sitename
	 * Some hosts will block outgoing mail from this address if it doesn't exist but
	 * there's no easy alternative. Defaulting to admin_email might appear to be another
	 * option but some hosts may refuse to relay mail from an unknown domain. See
	 * http://trac.wordpress.org/ticket/5007.
	 */
	// Get the site domain and get rid of www.
	$sitename = strtolower( $_SERVER['SERVER_NAME'] );
	if ( substr( $sitename, 0, 4 ) == 'www.' ) {
		$sitename = substr( $sitename, 4 );
	}
	$wp_from_email = 'wordpress@' . $sitename;
	if (empty($from_email) || $from_email == $wp_from_email) {
		if ($st_smtp_config['overwrite_sender'] == "overwrite_wp_default" && !empty($st_smtp_config['sender_mail'])) {
			$from_name = $st_smtp_config['sender_name'];
			$from_email = $st_smtp_config['sender_mail'];
		}
		else {
			$from_email = $wp_from_email;
		}
	}

	if (!empty($sender_email) && $sender_email == $wp_from_email && $st_smtp_config['overwrite_sender'] == "overwrite_wp_default" && !empty($st_smtp_config['sender_mail'])) {
		$sender_name = $st_smtp_config['sender_name'];
		$sender_email = $st_smtp_config['sender_mail'];
	}

	// Create a message
	$swift_message = new Swift_Message($subject);
	$swift_message->setFrom(array(apply_filters('wp_mail_from', $from_email) => apply_filters('wp_mail_from_name', $from_name)))
		->setBody($message)
	;

	// Set the sender, which may be different than the from field
	if (!empty($sender_email)) {
		$swift_message->setSender(array($sender_email => $sender_name));
	}

	// Set the reply-to
	if (!empty($reply_to)) {
		$swift_message->setReplyTo($reply_to);
	}

	// Set destination addresses
	if (!is_array($to))
		$to = explode( ',', $to);

	foreach ((array)$to as $recipient) {
		// Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
		$recipient_name = '';
		if( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
			if ( count( $matches ) == 3 ) {
				$recipient_name = $matches[1];
				$recipient = $matches[2];
			}
		}
		// leave the name null if empty
		if (empty($recipient_name)) {
			$swift_message->addTo(trim($recipient));
		}
		else {
			$swift_message->addTo(trim($recipient), $recipient_name);
		}
		
	}

	// Add any CC and BCC recipients
	if (!empty($cc)) {
		foreach ((array) $cc as $recipient) {
			// Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
			$recipient_name = '';
			if( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
				if ( count( $matches ) == 3 ) {
					$recipient_name = $matches[1];
					$recipient = $matches[2];
				}
			}
			// leave the name null if empty
			if (empty($recipient_name)) {
				$swift_message->addCc(trim($recipient));
			}
			else {
				$swift_message->addCc(trim($recipient), $recipient_name);
			}
		}
	}

	if (!empty($bcc)) {
		foreach ((array) $bcc as $recipient) {
			// Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
			$recipient_name = '';
			if( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
				if ( count( $matches ) == 3 ) {
					$recipient_name = $matches[1];
					$recipient = $matches[2];
				}
			}
			// leave the name null if empty
			if (empty($recipient_name)) {
				$swift_message->addBcc(trim($recipient));
			}
			else {
				$swift_message->addBcc(trim($recipient), $recipient_name);
			}
		}
	}

	// Set Content-Type and charset
	// If we don't have a content-type from the input headers
	if (empty($content_type))
		$content_type = 'text/plain';

	$content_type = apply_filters('wp_mail_content_type', $content_type);

	$swift_message->setContentType($content_type);

	// If we don't have a charset from the input headers
	if ( !isset( $charset ) )
		$charset = get_bloginfo('charset');

	// Set the content-type and charset
	Swift_Preferences::getInstance()->setCharset(apply_filters('wp_mail_charset', $charset));

	// Set custom headers
	if ( !empty( $headers ) ) {
		$msg_headers = $swift_message->getHeaders();

		foreach((array) $headers as $name => $content) {
			$msg_headers->addTextHeader($name, $content);
		}

		if (false !== stripos($content_type, 'multipart') && ! empty($boundary))
			$msg_headers->addTextHeader("Content-Type", sprint("%s;\n\t boundary=\"%s\"", $content_type, $boundary));
	}

	if (!empty($attachments)) {
		foreach ($attachments as $attachment) {
			// bug in Swift Mailer https://github.com/swiftmailer/swiftmailer/issues/274
			// they claim they fixed it, but at the end they throw the exception too late
			// we want to never fail for this silly error
			if (!is_readable($attachment))
				continue;
			try {
				$swift_message->attach(Swift_Attachment::fromPath($attachment));
			} catch (Swift_IoException $e) {
				continue;
			}
		}
	}

	// default server if none inserted
	if (empty($st_smtp_config['server']))
		$st_smtp_config['server'] = "localhost";

	// default port if none inserted
	if (empty($st_smtp_config['port']))
		$st_smtp_config['port'] = 25;

	// we should try first and _maybe_ echo failure error
	try {
		// Create the Transport then call setUsername() and setPassword()
		$transport = new Swift_SmtpTransport($st_smtp_config['server'], $st_smtp_config['port']);

		if (!empty($st_smtp_config['ssl']))
			$transport->setEncryption($st_smtp_config['ssl']);

		if (!empty($st_smtp_config['username']))
			$transport->setUsername($st_smtp_config['username']);

		if (!empty($st_smtp_config['password']))
			$transport->setPassword($st_smtp_config['password']);

		// Create the Mailer using your created Transport
		$mailer = new Swift_Mailer($transport);

		// Send!
		$result = $mailer->send($swift_message, $failures);
	}
	catch (Exception $e) {
		$result = false;
		if ($echo_error) {
			esc_html_e($e->getMessage());
		}
	}

	return $result;
}
}
?>