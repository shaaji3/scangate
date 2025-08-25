<?php
require_once __DIR__ . '/../config/config.php';
class EmailTemplate
{
    private static $companyName = APP_INFO['app_name'];
    private static $companyUrl = APP_INFO['app_url'];
    private static $companyLogo = 'https://collection.nisepa.ni.gov.ng/logo-dark.png';
    private static $companySupport = APP_INFO['app_emails']['otp'];

    private static function renderTemplate($subject, $message)
    {
        return "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>{$subject}</title>
            <style>
                .container {max-width:600px; margin:1px auto 40px auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
                .header { margin-top: 35px; padding-top:20px; text-align:center; }
                .header img { max-width:150px; }
                .content { padding:30px; font-size:15px; color:#333; line-height:1.6; }
                .content h1 { font-size:20px; margin-bottom:20px; color:#1a73e8; }
                .button { display:inline-block; padding:12px 20px; background:#1a73e8; color:#fff !important; text-decoration:none; border-radius:4px; font-weight:bold; margin-top:20px; }
                .footer { background:#f1f1f1; padding:15px; text-align:center; font-size:12px; color:#777; }
                .footer a { color:#1a73e8; text-decoration:none; }
            </style>
        </head>
        <body style='font-family: Arial, sans-serif; background-color: #F0F8FF; margin:0; padding:0;'>
                <div class='header'>
                    <a href='" . self::$companyUrl . "'><img src='" . self::$companyLogo . "' alt='" . self::$companyName . "'></a>
                </div>
                <div class='container'>
                <div class='content'>
                    {$message}
                </div>
                <div class='footer'>
                    &copy; " . date("Y") . " " . self::$companyName . ". All rights reserved.  
                    <br>
                    <a href='" . self::$companyUrl . "'>Visit our website</a>
                </div>
            </div>
        </body>
        </html>";
    }

    public static function otp($username, $otpCode)
    {
        $subject = "Your OTP Code";
        $message = "
            <h1>Hello {$username},</h1>
            <p>Please use the one-time-password (OTP) below:</p> 
            <h2 style='font-size: 24px; font-weight: bold; background-color: #f8f9fa; padding: 15px; text-align: center; border-radius: 8px; border: 1px dashed #007bff;color: #007bff;'>{$otpCode}</h2>
            <p>The OTP will expire in 10 minutes.</p>
            <div style='width:100%;border-bottom:1px solid #ccd1d6;margin:24px 0'></div>
            <p> If you did not initiate this OTP request, we strongly 
            advise you reset your password immediately to secure your account and notify us 
            as soon as possible via " . self::$companySupport . ".
            </p>
            <p style='font-size:16px;color:#57584e;margin:0;margin-top:24px'>Thank you.</p>
            
        ";
        return self::renderTemplate($subject, $message);
    }

    public static function resetPassword($username, $resetLink)
    {
        $subject = "Password Reset Request";
        $message = "
            <h1>Hello {$username},</h1>
            <p>We received a request to reset your password. Click the button below to set a new one:</p>
            <p style='text-align:center;'>
                <a href='{$resetLink}' class='button'>Reset Password</a>
            </p>
            <div style='width:100%;border-bottom:1px solid #ccd1d6;margin:24px 0'></div>
            <p> If you did not initiate this request, we strongly 
            advise you reset your password immediately to secure your account and notify us 
            as soon as possible via " . self::$companySupport . ".
            </p>
            <p style='font-size:16px;color:#57584e;margin:0;margin-top:24px'>Thank you.</p>
            
        ";
        return self::renderTemplate($subject, $message);
    }

    public static function newAccount($username, $verifyLink)
    {
        $subject = "Welcome to " . self::$companyName . "!";
        $message = "
            <h1>Welcome, {$username}!</h1>
            <p>Your staff account has been created successfully. To complete your registration and 
            activate your access to the system, please click the link below:</p>
            <p style='text-align:center;'>
                <a href='" . $verifyLink . "' class='button'>Complete Registration</a>
            </p>
            <p>Once completed, you’ll be able to log in and access all staff resources and tools.</p>
        ";
        return self::renderTemplate($subject, $message);
    }

    public static function custom($title, $message)
    {
        return self::renderTemplate($title, $message);
    }
}
