<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    // ─── Config SMTP partagée ────────────────────────────────────────────────
    private static string $host     = 'mail16.lwspanel.com';
    private static string $username = 'no-reply@aliadjame.com';
    private static string $password = '0Objectif-';
    private static int    $port     = 465;

    // ─── Initialise un objet PHPMailer prêt à l'emploi ──────────────────────
    private static function createMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = self::$host;
        $mail->SMTPAuth   = true;
        $mail->Username   = self::$username;
        $mail->Password   = self::$password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = self::$port;
        $mail->CharSet    = 'UTF-8';
        $mail->Encoding   = 'base64';
        $mail->setFrom(self::$username, 'Next Stock');
        return $mail;
    }

    // =========================================================================
    // 📧 INVITATION — envoyée quand un admin crée un nouvel utilisateur
    // =========================================================================
    public static function sendInvitation(string $email, string $prenom, string $nomEntreprise, string $token): bool
    {
        $mail = self::createMailer();

        try {
            $mail->addAddress($email, $prenom);
            $mail->isHTML(true);
            $mail->Subject = "✉️ Votre invitation pour rejoindre $nomEntreprise sur Next Stock";

            $prenomSafe     = htmlspecialchars($prenom);
            $entrepriseSafe = htmlspecialchars($nomEntreprise);
            $tokenSafe      = htmlspecialchars($token);
            $tokenUrl       = urlencode($token);

            $mail->Body = <<<HTML
            <!DOCTYPE html>
            <html lang="fr">
            <head>
            <meta charset="UTF-8"/>
            <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
            <title>Invitation Next Stock</title>
            </head>
            <body style="margin:0;padding:0;background-color:#f0f4ff;font-family:'Segoe UI',Helvetica,Arial,sans-serif;">

            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0f4ff;padding:48px 16px;">
                <tr><td align="center">

                <table width="560" cellpadding="0" cellspacing="0" border="0"
                    style="background-color:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 4px 40px rgba(16,185,129,0.10);">

                    <!-- Header -->
                    <tr>
                    <td style="background:rgb(16,185,129);padding:36px 40px 32px 40px;">
                        <table cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td style="background:rgba(255,255,255,0.2);border-radius:12px;width:44px;height:44px;text-align:center;vertical-align:middle;">
                            <span style="color:#ffffff;font-size:22px;font-weight:800;line-height:44px;">N</span>
                            </td>
                            <td style="padding-left:12px;vertical-align:middle;">
                            <span style="color:#ffffff;font-size:20px;font-weight:700;letter-spacing:0.4px;">Next Stock</span>
                            </td>
                        </tr>
                        </table>
                        <p style="margin:24px 0 0 0;color:rgba(255,255,255,0.85);font-size:13px;font-weight:600;letter-spacing:1.8px;text-transform:uppercase;">Invitation</p>
                        <h1 style="margin:6px 0 0 0;color:#ffffff;font-size:28px;font-weight:800;line-height:1.3;">Bonjour {$prenomSafe}&nbsp;👋</h1>
                    </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                    <td style="padding:36px 40px 0 40px;">
                        <p style="margin:0 0 24px 0;color:#4b5675;font-size:15px;line-height:1.75;">
                        <strong style="color:#1a202c;">{$entrepriseSafe}</strong> vous invite à rejoindre leur espace de gestion de stock sur <strong style="color:#10b981;">Next Stock</strong>.
                        <br/>Utilisez le code ci-dessous ou cliquez sur le bouton pour accéder à votre compte.
                        </p>
                    </td>
                    </tr>

                    <!-- Token -->
                    <tr>
                    <td style="padding:0 40px;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td style="background:#10b9812b;border:1.5px dashed #10b981;border-radius:14px;padding:26px;text-align:center;">
                            <p style="margin:0 0 10px 0;color:#6b7da8;font-size:11px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;">Code d'invitation</p>
                            <p style="margin:0;color:#1e7f5f;font-size:34px;font-weight:800;letter-spacing:8px;font-family:'Courier New',monospace;">{$tokenSafe}</p>
                            </td>
                        </tr>
                        </table>
                    </td>
                    </tr>

                    <!-- CTA -->
                    <tr>
                    <td style="padding:28px 40px 0 40px;text-align:center;">
                        <a href="https://nextstock.app/invitation?token={$tokenUrl}"
                        style="display:inline-block;background:linear-gradient(135deg,#10b981 0%,#059669 100%);color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;padding:15px 40px;border-radius:12px;letter-spacing:0.3px;box-shadow:0 4px 20px rgba(16,185,129,0.35);">
                        Rejoindre l'espace →
                        </a>
                    </td>
                    </tr>

                    <!-- Note sécurité -->
                    <tr>
                    <td style="padding:24px 40px 0 40px;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td style="background:#d7f3ea61;border-left:3px solid #10b981;border-radius:0 10px 10px 0;padding:14px 16px;">
                            <p style="margin:0;color:#5b7ba8;font-size:12px;line-height:1.7;">
                                🔒 <strong style="color:#1e3a5f;">Lien sécurisé</strong> — Si vous n'êtes pas à l'origine de cette invitation, ignorez simplement cet email.
                            </p>
                            </td>
                        </tr>
                        </table>
                    </td>
                    </tr>

                    <!-- Divider -->
                    <tr><td style="padding:32px 40px 0 40px;"><hr style="border:none;border-top:1px solid #e8eef8;margin:0;"/></td></tr>

                    <!-- Footer -->
                    <tr>
                    <td style="padding:24px 40px 36px 40px;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td>
                            <p style="margin:0 0 3px 0;color:#1a202c;font-size:13px;font-weight:700;">L'équipe Next Stock</p>
                            <p style="margin:0;color:#9ba3b5;font-size:12px;">Gestion de stock intelligente</p>
                            </td>
                            <td align="right" style="vertical-align:middle;">
                            <a href="https://nextstock.app" style="color:#10b981;font-size:12px;font-weight:600;text-decoration:none;">nextstock.app</a>
                            </td>
                        </tr>
                        </table>
                    </td>
                    </tr>

                </table>

                <p style="color:#b0bcd4;font-size:11px;margin:20px 0 0 0;text-align:center;">
                    Cet email a été envoyé automatiquement par Next Stock · Ne pas répondre
                </p>

                </td></tr>
            </table>

            </body>
            </html>
            HTML;

            $mail->AltBody = "Bonjour $prenom,\n\n$nomEntreprise vous invite à rejoindre leur espace sur Next Stock.\n\nCode d'invitation : $token\n\nLien : https://nextstock.app/invitation?token=$tokenUrl\n\nL'équipe Next Stock";

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log("Mailer::sendInvitation — Erreur : {$mail->ErrorInfo}");
            return false;
        }
    }

    public static function sendRegister(string $email, string $prenom, string $nomEntreprise, string $token): bool
    {
        $mail = self::createMailer();

        try {
            $mail->addAddress($email, $prenom);
            $mail->isHTML(true);
            $mail->Subject = "🎉 Inscription réussie — Bienvenue sur Next Stock !";

            $prenomSafe     = htmlspecialchars($prenom);
            $entrepriseSafe = htmlspecialchars($nomEntreprise);

            $mail->Body = <<<HTML
            <!DOCTYPE html>
            <html lang="fr">
            <head>
            <meta charset="UTF-8"/>
            <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
            <title>Inscription Next Stock</title>
            </head>
            <body style="margin:0;padding:0;background-color:#f0f4ff;font-family:'Segoe UI',Helvetica,Arial,sans-serif;">

            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0f4ff;padding:48px 16px;">
                <tr><td align="center">

                <table width="560" cellpadding="0" cellspacing="0" border="0"
                    style="background-color:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 4px 40px rgba(16,185,129,0.10);">

                    <!-- Header -->
                    <tr>
                    <td style="background:rgb(16,185,129);padding:36px 40px 32px 40px;">
                        <table cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td style="background:rgba(255,255,255,0.2);border-radius:12px;width:44px;height:44px;text-align:center;vertical-align:middle;">
                            <span style="color:#ffffff;font-size:22px;font-weight:800;line-height:44px;">N</span>
                            </td>
                            <td style="padding-left:12px;vertical-align:middle;">
                            <span style="color:#ffffff;font-size:20px;font-weight:700;letter-spacing:0.4px;">Next Stock</span>
                            </td>
                        </tr>
                        </table>
                        <p style="margin:24px 0 0 0;color:rgba(255,255,255,0.85);font-size:13px;font-weight:600;letter-spacing:1.8px;text-transform:uppercase;">Inscription confirmée</p>
                        <h1 style="margin:6px 0 0 0;color:#ffffff;font-size:28px;font-weight:800;line-height:1.3;">Bienvenue {$prenomSafe}&nbsp;🎉</h1>
                    </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                    <td style="padding:36px 40px 0 40px;">
                        <p style="margin:0 0 16px 0;color:#4b5675;font-size:15px;line-height:1.75;">
                        Votre inscription sur <strong style="color:#10b981;">Next Stock</strong> pour l'entreprise <strong style="color:#1a202c;">{$entrepriseSafe}</strong> a bien été enregistrée.
                        </p>
                        <p style="margin:0 0 24px 0;color:#4b5675;font-size:15px;line-height:1.75;">
                        Vous pouvez dès maintenant accéder à votre espace de gestion de stock en cliquant sur le bouton ci-dessous.
                        </p>
                    </td>
                    </tr>

                    <!-- CTA -->
                    <tr>
                    <td style="padding:0 40px 0 40px;text-align:center;">
                        <a href="https://aliadjame.com"
                        style="display:inline-block;background:linear-gradient(135deg,#10b981 0%,#059669 100%);color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;padding:15px 40px;border-radius:12px;letter-spacing:0.3px;box-shadow:0 4px 20px rgba(16,185,129,0.35);">
                        Se connecter →
                        </a>
                    </td>
                    </tr>

                    <!-- Note sécurité -->
                    <tr>
                    <td style="padding:28px 40px 0 40px;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td style="background:#d7f3ea61;border-left:3px solid #10b981;border-radius:0 10px 10px 0;padding:14px 16px;">
                            <p style="margin:0;color:#5b7ba8;font-size:12px;line-height:1.7;">
                                🔒 <strong style="color:#1e3a5f;">Lien sécurisé</strong> — Si vous n'êtes pas à l'origine de cette inscription, ignorez simplement cet email.
                            </p>
                            </td>
                        </tr>
                        </table>
                    </td>
                    </tr>

                    <!-- Divider -->
                    <tr><td style="padding:32px 40px 0 40px;"><hr style="border:none;border-top:1px solid #e8eef8;margin:0;"/></td></tr>

                    <!-- Footer -->
                    <tr>
                    <td style="padding:24px 40px 36px 40px;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td>
                            <p style="margin:0 0 3px 0;color:#1a202c;font-size:13px;font-weight:700;">L'équipe Next Stock</p>
                            <p style="margin:0;color:#9ba3b5;font-size:12px;">Gestion de stock intelligente</p>
                            </td>
                            <td align="right" style="vertical-align:middle;">
                            <a href="https://aliadjame.com" style="color:#10b981;font-size:12px;font-weight:600;text-decoration:none;">aliadjame.com</a>
                            </td>
                        </tr>
                        </table>
                    </td>
                    </tr>

                </table>

                <p style="color:#b0bcd4;font-size:11px;margin:20px 0 0 0;text-align:center;">
                    Cet email a été envoyé automatiquement par Next Stock · Ne pas répondre
                </p>

                </td></tr>
            </table>

            </body>
            </html>
            HTML;

            $mail->AltBody = "Bonjour $prenom,\n\nVotre inscription sur Next Stock pour $nomEntreprise a bien été enregistrée.\n\nConnectez-vous ici : https://aliadjame.com\n\nL'équipe Next Stock";

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log("Mailer::sendRegister — Erreur : {$mail->ErrorInfo}");
            return false;
        }
    }

    public static function sendAccountUpdated(string $email, string $prenom, bool $passwordChanged = false): bool
    {
        $mail = self::createMailer();

        try {
            $mail->addAddress($email, $prenom);
            $mail->isHTML(true);
            $mail->Subject = "🔄 Votre compte Next Stock a été mis à jour";

            $prenomSafe      = htmlspecialchars($prenom);
            $passwordSection = '';

            if ($passwordChanged) {
                $passwordSection = <<<HTML
                <!-- Password changed notice -->
                <tr>
                <td style="padding:0 40px;">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="background:#fff7ed;border:1.5px dashed #f59e0b;border-radius:14px;padding:20px 24px;">
                        <p style="margin:0 0 6px 0;color:#92400e;font-size:12px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;">⚠️ Mot de passe modifié</p>
                        <p style="margin:0;color:#78350f;font-size:14px;line-height:1.6;">
                            Votre mot de passe a été mis à jour. Si vous n'êtes pas à l'origine de ce changement, contactez immédiatement votre administrateur.
                        </p>
                        </td>
                    </tr>
                    </table>
                </td>
                </tr>
                <tr><td style="padding:20px 0 0 0;"></td></tr>
                HTML;
            }

            $mail->Body = <<<HTML
            <!DOCTYPE html>
            <html lang="fr">
            <head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/></head>
            <body style="margin:0;padding:0;background-color:#f0f4ff;font-family:'Segoe UI',Helvetica,Arial,sans-serif;">

            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0f4ff;padding:48px 16px;">
                <tr><td align="center">

                <table width="560" cellpadding="0" cellspacing="0" border="0"
                    style="background-color:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 4px 40px rgba(99,102,241,0.10);">

                    <!-- Header -->
                    <tr>
                    <td style="background:linear-gradient(135deg,#6366f1 0%,#818cf8 100%);padding:36px 40px 32px 40px;">
                        <table cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td style="background:rgba(255,255,255,0.2);border-radius:12px;width:44px;height:44px;text-align:center;vertical-align:middle;">
                            <span style="color:#ffffff;font-size:22px;font-weight:800;line-height:44px;">N</span>
                            </td>
                            <td style="padding-left:12px;vertical-align:middle;">
                            <span style="color:#ffffff;font-size:20px;font-weight:700;letter-spacing:0.4px;">Next Stock</span>
                            </td>
                        </tr>
                        </table>
                        <p style="margin:24px 0 0 0;color:rgba(255,255,255,0.85);font-size:13px;font-weight:600;letter-spacing:1.8px;text-transform:uppercase;">Compte mis à jour</p>
                        <h1 style="margin:6px 0 0 0;color:#ffffff;font-size:26px;font-weight:800;line-height:1.3;">
                        Bonjour {$prenomSafe}&nbsp;🔄
                        </h1>
                    </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                    <td style="padding:36px 40px 24px 40px;">
                        <p style="margin:0;color:#4b5675;font-size:15px;line-height:1.75;">
                        Les informations de votre compte sur <strong style="color:#6366f1;">Next Stock</strong> ont été mises à jour par votre administrateur.
                        <br/>Si vous avez des questions, rapprochez-vous de lui directement.
                        </p>
                    </td>
                    </tr>

                    <!-- Changements résumé -->
                    <tr>
                    <td style="padding:0 40px;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td style="background:#f5f3ff;border-left:3px solid #6366f1;border-radius:0 10px 10px 0;padding:16px 20px;">
                            <p style="margin:0;color:#4338ca;font-size:13px;line-height:1.8;font-weight:500;">
                                ✅ Informations du profil mises à jour<br/>
                                ✅ Droits d'accès mis à jour
                            </p>
                            </td>
                        </tr>
                        </table>
                    </td>
                    </tr>

                    <tr><td style="padding:20px 0 0 0;"></td></tr>

                    {$passwordSection}

                    <!-- CTA -->
                    <tr>
                    <td style="padding:0 40px;text-align:center;">
                        <a href="https://nextstock.app/login"
                        style="display:inline-block;background:linear-gradient(135deg,#6366f1 0%,#818cf8 100%);color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;padding:15px 40px;border-radius:12px;letter-spacing:0.3px;box-shadow:0 4px 20px rgba(99,102,241,0.30);">
                        Accéder à mon espace →
                        </a>
                    </td>
                    </tr>

                    <!-- Security note -->
                    <tr>
                    <td style="padding:24px 40px 0 40px;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td style="background:#eef2ff;border-left:3px solid #6366f1;border-radius:0 10px 10px 0;padding:14px 16px;">
                            <p style="margin:0;color:#5b7ba8;font-size:12px;line-height:1.7;">
                                🔒 <strong style="color:#1e3a5f;">Vous n'avez rien demandé ?</strong> — Contactez immédiatement votre administrateur si cette modification ne vous a pas été annoncée.
                            </p>
                            </td>
                        </tr>
                        </table>
                    </td>
                    </tr>

                    <!-- Divider -->
                    <tr><td style="padding:32px 40px 0 40px;"><hr style="border:none;border-top:1px solid #e8eef8;margin:0;"/></td></tr>

                    <!-- Footer -->
                    <tr>
                    <td style="padding:24px 40px 36px 40px;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td>
                            <p style="margin:0 0 3px 0;color:#1a202c;font-size:13px;font-weight:700;">L'équipe Next Stock</p>
                            <p style="margin:0;color:#9ba3b5;font-size:12px;">Gestion de stock intelligente</p>
                            </td>
                            <td align="right" style="vertical-align:middle;">
                            <a href="https://nextstock.app" style="color:#6366f1;font-size:12px;font-weight:600;text-decoration:none;">nextstock.app</a>
                            </td>
                        </tr>
                        </table>
                    </td>
                    </tr>

                </table>

                <p style="color:#b0bcd4;font-size:11px;margin:20px 0 0 0;text-align:center;">
                    Cet email a été envoyé automatiquement par Next Stock · Ne pas répondre
                </p>

                </td></tr>
            </table>

            </body>
            </html>
            HTML;

            $passwordNote = $passwordChanged ? "\n⚠️ Votre mot de passe a également été modifié." : "";
            $mail->AltBody = "Bonjour $prenom,\n\nVotre compte Next Stock a été mis à jour par votre administrateur.$passwordNote\n\nConnectez-vous : https://nextstock.app/login\n\nL'équipe Next Stock";

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log("Mailer::sendAccountUpdated — Erreur : {$mail->ErrorInfo}");
            return false;
        }
    }



    // =========================================================================
    // 🔑 RÉINITIALISATION MOT DE PASSE
    // =========================================================================
    public static function sendResetPassword(string $email, string $prenom, string $resetLink): bool
    {
        $mail = self::createMailer();

        try {
            $mail->addAddress($email, $prenom);
            $mail->isHTML(true);
            $mail->Subject = "🔑 Réinitialisation de votre mot de passe — Next Stock";

            $prenomSafe   = htmlspecialchars($prenom);
            $resetLinkSafe = htmlspecialchars($resetLink);

            $mail->Body = <<<HTML
            <!DOCTYPE html>
            <html lang="fr">
            <head><meta charset="UTF-8"/></head>
            <body style="margin:0;padding:0;background-color:#f0f4ff;font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0f4ff;padding:48px 16px;">
                <tr><td align="center">
                <table width="560" cellpadding="0" cellspacing="0" border="0"
                    style="background-color:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 4px 40px rgba(245,158,11,0.10);">

                    <!-- Header -->
                    <tr>
                    <td style="background:linear-gradient(135deg,#f59e0b 0%,#f97316 100%);padding:36px 40px 32px 40px;">
                        <table cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td style="background:rgba(255,255,255,0.2);border-radius:12px;width:44px;height:44px;text-align:center;vertical-align:middle;">
                            <span style="color:#ffffff;font-size:22px;font-weight:800;line-height:44px;">N</span>
                            </td>
                            <td style="padding-left:12px;vertical-align:middle;">
                            <span style="color:#ffffff;font-size:20px;font-weight:700;">Next Stock</span>
                            </td>
                        </tr>
                        </table>
                        <p style="margin:24px 0 0 0;color:rgba(255,255,255,0.85);font-size:13px;font-weight:600;letter-spacing:1.8px;text-transform:uppercase;">Sécurité</p>
                        <h1 style="margin:6px 0 0 0;color:#ffffff;font-size:26px;font-weight:800;">Réinitialisation du mot de passe</h1>
                    </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                    <td style="padding:36px 40px 0 40px;">
                        <p style="margin:0 0 24px 0;color:#4b5675;font-size:15px;line-height:1.75;">
                        Bonjour <strong style="color:#1a202c;">{$prenomSafe}</strong>,<br/><br/>
                        Vous avez demandé la réinitialisation de votre mot de passe. Cliquez sur le bouton ci-dessous pour en choisir un nouveau.
                        <br/>Ce lien est valable <strong>30 minutes</strong>.
                        </p>
                    </td>
                    </tr>

                    <!-- CTA -->
                    <tr>
                    <td style="padding:8px 40px 0 40px;text-align:center;">
                        <a href="{$resetLinkSafe}"
                        style="display:inline-block;background:linear-gradient(135deg,#f59e0b 0%,#f97316 100%);color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;padding:15px 40px;border-radius:12px;box-shadow:0 4px 20px rgba(245,158,11,0.35);">
                        Réinitialiser mon mot de passe →
                        </a>
                    </td>
                    </tr>

                    <!-- Note -->
                    <tr>
                    <td style="padding:24px 40px 0 40px;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td style="background:#fff7ed;border-left:3px solid #f59e0b;border-radius:0 10px 10px 0;padding:14px 16px;">
                            <p style="margin:0;color:#5b7ba8;font-size:12px;line-height:1.7;">
                                ⚠️ <strong style="color:#1e3a5f;">Si vous n'êtes pas à l'origine de cette demande</strong>, ignorez cet email. Votre mot de passe ne sera pas modifié.
                            </p>
                            </td>
                        </tr>
                        </table>
                    </td>
                    </tr>

                    <tr><td style="padding:32px 40px 0 40px;"><hr style="border:none;border-top:1px solid #e8eef8;margin:0;"/></td></tr>
                    <tr>
                    <td style="padding:24px 40px 36px 40px;">
                        <p style="margin:0 0 3px 0;color:#1a202c;font-size:13px;font-weight:700;">L'équipe Next Stock</p>
                        <p style="margin:0;color:#9ba3b5;font-size:12px;">Gestion de stock intelligente</p>
                    </td>
                    </tr>

                </table>
                <p style="color:#b0bcd4;font-size:11px;margin:20px 0 0 0;text-align:center;">Cet email a été envoyé automatiquement · Ne pas répondre</p>
                </td></tr>
            </table>
            </body></html>
            HTML;

            $mail->AltBody = "Bonjour $prenom,\n\nRéinitialisez votre mot de passe ici : $resetLink\n\nCe lien expire dans 30 minutes.\n\nL'équipe Next Stock";

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log("Mailer::sendResetPassword — Erreur : {$mail->ErrorInfo}");
            return false;
        }
    }


    // =========================================================================
    // ✅ CONFIRMATION DE COMPTE
    // =========================================================================
    public static function sendWelcome(string $email, string $prenom, string $nomEntreprise): bool
    {
        $mail = self::createMailer();

        try {
            $mail->addAddress($email, $prenom);
            $mail->isHTML(true);
            $mail->Subject = "✅ Bienvenue sur Next Stock, $prenom !";

            $prenomSafe     = htmlspecialchars($prenom);
            $entrepriseSafe = htmlspecialchars($nomEntreprise);

            $mail->Body = <<<HTML
            <!DOCTYPE html>
            <html lang="fr">
            <head><meta charset="UTF-8"/></head>
            <body style="margin:0;padding:0;background-color:#f0f4ff;font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0f4ff;padding:48px 16px;">
                <tr><td align="center">
                <table width="560" cellpadding="0" cellspacing="0" border="0"
                    style="background-color:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 4px 40px rgba(99,102,241,0.10);">

                    <!-- Header -->
                    <tr>
                    <td style="background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);padding:36px 40px 32px 40px;">
                        <table cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td style="background:rgba(255,255,255,0.2);border-radius:12px;width:44px;height:44px;text-align:center;vertical-align:middle;">
                            <span style="color:#ffffff;font-size:22px;font-weight:800;line-height:44px;">N</span>
                            </td>
                            <td style="padding-left:12px;vertical-align:middle;">
                            <span style="color:#ffffff;font-size:20px;font-weight:700;">Next Stock</span>
                            </td>
                        </tr>
                        </table>
                        <p style="margin:24px 0 0 0;color:rgba(255,255,255,0.85);font-size:13px;font-weight:600;letter-spacing:1.8px;text-transform:uppercase;">Bienvenue</p>
                        <h1 style="margin:6px 0 0 0;color:#ffffff;font-size:28px;font-weight:800;">Compte activé 🎉</h1>
                    </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                    <td style="padding:36px 40px 0 40px;">
                        <p style="margin:0 0 24px 0;color:#4b5675;font-size:15px;line-height:1.75;">
                        Bonjour <strong style="color:#1a202c;">{$prenomSafe}</strong>,<br/><br/>
                        Votre compte sur l'espace <strong style="color:#6366f1;">{$entrepriseSafe}</strong> est maintenant actif. Vous pouvez vous connecter à tout moment sur <strong>Next Stock</strong> et commencer à gérer votre stock.
                        </p>
                    </td>
                    </tr>

                    <!-- CTA -->
                    <tr>
                    <td style="padding:8px 40px 0 40px;text-align:center;">
                        <a href="https://nextstock.app/login"
                        style="display:inline-block;background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;padding:15px 40px;border-radius:12px;box-shadow:0 4px 20px rgba(99,102,241,0.35);">
                        Accéder à mon espace →
                        </a>
                    </td>
                    </tr>

                    <tr><td style="padding:32px 40px 0 40px;"><hr style="border:none;border-top:1px solid #e8eef8;margin:0;"/></td></tr>
                    <tr>
                    <td style="padding:24px 40px 36px 40px;">
                        <p style="margin:0 0 3px 0;color:#1a202c;font-size:13px;font-weight:700;">L'équipe Next Stock</p>
                        <p style="margin:0;color:#9ba3b5;font-size:12px;">Gestion de stock intelligente</p>
                    </td>
                    </tr>

                </table>
                <p style="color:#b0bcd4;font-size:11px;margin:20px 0 0 0;text-align:center;">Cet email a été envoyé automatiquement · Ne pas répondre</p>
                </td></tr>
            </table>
            </body></html>
            HTML;

            $mail->AltBody = "Bonjour $prenom,\n\nVotre compte est actif ! Connectez-vous : https://nextstock.app/login\n\nL'équipe Next Stock";

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log("Mailer::sendWelcome — Erreur : {$mail->ErrorInfo}");
            return false;
        }
    }

    // =========================================================================
    // ✉️ Gabarit générique pour notifications simples (HTML)
    //    Utilisé par les modules comptabilités / gestion bancaire
    // =========================================================================
    public static function sendSimpleHtml(string $email, string $subject, string $htmlBody): bool
    {
        $mail = self::createMailer();

        try {
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = strip_tags($htmlBody);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer::sendSimpleHtml — Erreur : {$mail->ErrorInfo}");
            return false;
        }
    }
}