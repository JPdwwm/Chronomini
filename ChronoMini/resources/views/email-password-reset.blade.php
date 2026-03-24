<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de votre mot de passe</title>
    <style>
        body {
            font-family: Georgia, 'Times New Roman', Times, serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            color: #666;
            line-height: 1.6;
        }
        
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.1);
        }
        
        .header {
            background-color: #78BB99;
            padding: 20px;
            text-align: center;
        }
        
        .content {
            padding: 30px;
        }
        
        h1, h2 {
            color: #78BB99;
            margin-bottom: 20px;
        }
        
        p {
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        
        .button {
            display: inline-block;
            background-color: #358E9D;
            color: white !important;
            text-decoration: none;
            padding: 15px 25px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .button:hover {
            background-color: #286C72;
        }
        
        .footer {
            background-color: #f5f5f5;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1 style="color: white; margin: 0;">ChronoMini</h1>
        </div>
        
        <div class="content">
            <h2>Réinitialisation de mot de passe</h2>
            
            <p>Bonjour {{ $user->first_name }},</p>
            
            <p>Vous avez demandé la réinitialisation de votre mot de passe. Veuillez cliquer sur le bouton ci-dessous pour définir un nouveau mot de passe :</p>
            
            <div class="button-container">
                <a href="{{ $frontend_URL }}/reset-password?email={{ $user->email }}&token={{ $user->reset_token }}" class="button">Réinitialiser mon mot de passe</a>
            </div>
            
            <p>Ce lien expirera dans 24 heures pour des raisons de sécurité.</p>
            
            <p>Si vous n'avez pas demandé cette réinitialisation, vous pouvez ignorer ce message en toute sécurité.</p>
        </div>
        
        <div class="footer">
            <p>&copy; 2025 ChronoMini. Tous droits réservés.</p>
            <p>Ce message a été envoyé automatiquement, merci de ne pas y répondre.</p>
        </div>
    </div>
</body>
</html>