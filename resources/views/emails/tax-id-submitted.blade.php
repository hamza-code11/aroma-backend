<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tax ID Submitted</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #6b7a3f;">Hello {{ $taxId->user->name }},</h2>
        <p>Thank you for submitting your Tax ID for verification.</p>
        
        <div style="background-color: #f9f9f9; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 0;"><strong>Tax ID:</strong> {{ $taxId->tax_id }}</p>
            <p style="margin: 0;"><strong>Status:</strong> <span style="color: #f59e0b;">Pending Verification</span></p>
        </div>
        
        <p>Our team is currently reviewing your Tax ID. You will receive an email once it has been approved or rejected. This usually takes 1-2 business days.</p>
        
        <p>Best regards,<br>The i.Shine Aroma Team</p>
    </div>
</body>
</html>
