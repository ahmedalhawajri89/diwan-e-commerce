<!DOCTYPE html>
<html>
<head>
    <title>New Contact Message</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 5px; }
        .header { background-color: #f8f9fa; padding: 15px; text-align: center; border-bottom: 2px solid #333; }
        .content { padding: 20px 0; }
        .footer { font-size: 12px; text-align: center; color: #777; margin-top: 20px; border-top: 1px solid #eee; padding-top: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #eee; text-align: left; }
        th { width: 30%; color: #555; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>New Contact Submission</h2>
        </div>
        <div class="content">
            <p>Hello Admin,</p>
            <p>You have received a new message from the contact form on your website.</p>
            
            <table>
                <tr>
                    <th>Name:</th>
                    <td>{{ $contact->name }}</td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td>{{ $contact->email }}</td>
                </tr>
                <tr>
                    <th>Subject:</th>
                    <td>{{ $contact->subject }}</td>
                </tr>
            </table>
            
            <h4 style="margin-top: 20px; border-bottom: 1px solid #eee; padding-bottom: 5px;">Message:</h4>
            <p style="background: #f9f9f9; padding: 15px; border-radius: 5px; white-space: pre-wrap;">{{ $contact->message }}</p>
        </div>
        <div class="footer">
            <p>This email was automatically generated from the Diwan contact form.</p>
        </div>
    </div>
</body>
</html>
