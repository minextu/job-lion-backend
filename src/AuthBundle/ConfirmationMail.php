<?php namespace JobLion\AuthBundle;

use JobLion\AppBundle\Entity\User;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailerException;

class ConfirmationMail
{
    /**
     * Send a confirmation E-Mail containing instructions
     * on how to activate this account
     *
     * @param  User   $user    User to send email to
     * @param  bool   $isTest  If true no actual email will be send
     */
    public static function accountActivation(User $user, $isTest=false)
    {
        // quick email check
        if (!filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
            throw new Exception("E-Mail is not valid");
        }
        // user id check
        if (!$user->getId()) {
            throw new Exception("User has not been saved to database, yet");
        }

        // generate a random code
        $code = self::generateRandomCode();

        // save code to database
        $user->setActivationCode($code);

        // generate the activation url
        $url = "http://" . $_SERVER['SERVER_NAME'] . "/Aktivieren/" . $user->getId() . "?code=" . $code;

        // set subject and text
        $subject = "Willkommen bei JobLion! Bitte die E-Mailadresse bestätigen";
        $text = "<h1>Willkommen bei JobLion</h1>
					<p>
						Danke für die Registrierung bei JobLion! Um das Konto zu aktivieren, bitte auf den Link unter diesem Text klicken.<br>
						Dadurch kann die E-Mail Adresse verifiziert werden.
					</p>
					<a href='$url'>E-Mail bestätigen</a><br>(<a href='$url'>$url</a>)<br>
          Oder Bestätigungscode manuell eingeben: <i>$code</i>
					<p>
						Nach der Bestätigung ist das JobLion Konto direkt freigeschaltet.
					</p>
        ";

        // send the email
        self::sendMail($subject, $text, $user->getEmail(), $isTest);
    }

    /**
     * @return string random hexadecimal code
     */
    private static function generateRandomCode()
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Send the given text as email
     * @param  string  $subject
     * @param  string  $html       Text to send
     * @param  string  $recipient
     * @param  boolean $isTest     If true no actual email will be send
     */
    private static function sendMail($subject, $html, $recipient, $isTest)
    {
        $from = "noreply@" . $_SERVER['SERVER_NAME'];
        $fromName = "JobLion";

        if (!$isTest) {
            $mail = new PHPMailer(true);
            $mail->CharSet = 'utf-8';
            $mail->setFrom($from, $fromName);
            $mail->addAddress($recipient);
            $mail->Subject = $subject;
            $mail->msgHtml($html);
            $mail->send();
        }
    }
}
