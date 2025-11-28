<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Password Reset OTP</title>
    <style type="text/css">
        /* Basic Resets & Fluid Table Setup */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        
        /* Forces the container to be 100% wide for true fluid behavior */
        .email-container { 
            width: 100% !important; 
            max-width: 600px !important; 
        }

        /* General Styles */
        body { margin: 0; padding: 0; background-color: #f5f5f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* OTP Box Styles & Hover (Pseudo-Animation) */
        .otp-box {
            background: #f8f9fa;
            border: 2px solid #667eea;
            padding: 25px;
            text-align: center;
            border-radius: 10px;
            margin: 30px 0;
            transition: all 0.3s ease-in-out; 
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.3);
        }
        
        .otp-box:hover {
            border-color: #764ba2;
            box-shadow: 0 0 15px rgba(118, 75, 162, 0.8);
        }
        
        .otp-code {
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 10px;
            color: #667eea;
            font-family: 'Courier New', monospace;
            transition: color 0.3s ease-in-out;
        }

        .otp-box:hover .otp-code {
            color: #764ba2;
        }
        
        /* Mobile-Specific Styles (for smaller screens) */
        @media screen and (max-width: 600px) {
            .content-padding { padding: 20px 15px !important; }
            .otp-code { font-size: 28px !important; letter-spacing: 4px !important; }
            /* Ensure text aligns nicely on small devices */
            .header h1 { font-size: 20px !important; }
            p { font-size: 14px !important; }
        }

        /* Outlook Fixes */
        </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f5f5f5;">

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="min-width: 100%;">
    <tr>
        <td align="center" style="padding: 0;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="600" class="email-container" style="margin: auto; background-color: #ffffff; width: 100%;">
                
                <tr>
                    <td align="center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px 20px;">
                        <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: bold;" class="header">🔐 Password Reset Request</h1>
                    </td>
                </tr>
                
                <tr>
                    <td style="padding: 40px 30px;" class="content-padding">
                        <h2 style="color: #333; margin-top: 0; font-size: 20px;">Hello,</h2>
                        <p style="font-size: 16px; line-height: 1.6; color: #333;">We received a request to reset your **PathoLens** account password. Use the OTP code below to proceed:</p>
                        
                        <div class="otp-box">
                            <div class="otp-code">{{ $otp }}</div>
                        </div>
                        
                        <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px; font-size: 16px;">
                            <strong style="color: #856404;">⏰ This code will expire in {{ $expiryMinutes }} minutes.</strong>
                        </div>
                        
                        <p style="font-size: 16px; line-height: 1.6; color: #333;">Enter this code on the password reset page to continue.</p>
                        
                        <div style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 4px; font-size: 14px;">
                            <p style="margin: 5px 0; color: #721c24; font-weight: bold;">⚠️ Security Tips:</p>
                            <ul style="margin: 5px 0 0 20px; padding: 0; color: #721c24; list-style-type: disc;">
                                <li>Never share this OTP with anyone</li>
                                <li>PathoLens will never ask for your OTP via phone or email</li>
                                <li>If you suspect unauthorized access, contact support immediately</li>
                            </ul>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #6c757d; border-top: 1px solid #dee2e6;">
                        <p style="margin: 0;">&copy; 2024 PathoLens. All rights reserved.</p>
                        <p style="margin: 5px 0 0 0;">This is an automated email. Please do not reply.</p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>