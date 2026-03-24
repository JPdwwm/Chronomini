<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau message de contact</title>
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
        
        .message-box {
            background-color: #f9f9f9;
            border-left: 4px solid #78BB99;
            padding: 15px;
            margin: 20px 0;
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
            <h2>Nouveau message de contact</h2>
            
            <p><strong>De:</strong> {{ $contact->name }} ({{ $contact->email }})</p>
            <p><strong>Sujet:</strong> {{ $contact->subject }}</p>
            
            <p><strong>Message:</strong></p>
            <div class="message-box">
                {{ $contact->messageContent }}
            </div>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} ChronoMini. Tous droits réservés.</p>
            <p>Ce message a été envoyé via le formulaire de contact de votre site.</p>
        </div>
    </div>
</body>
</html>