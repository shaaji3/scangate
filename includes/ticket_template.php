<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ticket</title>
    <style>
        body { font-family: sans-serif; }
        .ticket { border: 2px solid #000; padding: 20px; width: 100%; margin: auto; box-sizing: border-box; }
        .header { text-align: center; }
        .details { margin-top: 20px; }
        .qr-code { text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header">
            <h1><?php echo htmlspecialchars($ticket_data['event_title']); ?></h1>
            <p><strong>Official Event Ticket</strong></p>
        </div>
        <hr>
        <div class="details">
            <p><strong>Attendee:</strong> <?php echo htmlspecialchars($ticket_data['user_name']); ?></p>
            <p><strong>Ticket Type:</strong> <?php echo htmlspecialchars($ticket_data['ticket_type']); ?></p>
            <p><strong>Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($ticket_data['event_date'])); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($ticket_data['event_location']); ?></p>
        </div>
        <div class="qr-code">
            <img src="<?php echo $qr_code_path_absolute; ?>" alt="QR Code" width="200">
            <p><?php echo htmlspecialchars($ticket_data['ticket_code']); ?></p>
        </div>
    </div>
</body>
</html>
