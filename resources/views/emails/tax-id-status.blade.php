<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tax ID Status Update</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: {{ $status === 'verified' ? '#22c55e' : '#ef4444' }};">
            Hello {{ $taxId->user->name }},
        </h2>
        
        @if($status === 'verified')
            <p>Great news! Your Tax ID has been <strong style="color: #22c55e;">approved</strong>.</p>
            <p>You can now proceed to checkout and enjoy a seamless shopping experience.</p>
        @else
            <p>Unfortunately, your Tax ID has been <strong style="color: #ef4444;">rejected</strong>.</p>
            <p>Please log in to your account and submit a valid Tax ID to continue with checkout.</p>
        @endif
        
        <div style="background-color: #f9f9f9; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 0;"><strong>Tax ID:</strong> {{ $taxId->tax_id }}</p>
            <p style="margin: 0;"><strong>Status:</strong> {{ ucfirst($status) }}</p>
        </div>
        
        <p>Best regards,<br>The i.Shine Aroma Team</p>
    </div>
</body>
</html>
