document.addEventListener('DOMContentLoaded', function() {
    const resultDiv = document.getElementById('result');
    const readerDiv = document.getElementById('reader');

    if (!readerDiv) {
        console.error("Scanner element with id 'reader' not found.");
        return;
    }

    // This variable should be defined in the PHP view before this script is loaded
    if (typeof currentEventId === 'undefined') {
        console.error("Variable 'currentEventId' is not defined.");
        resultDiv.innerHTML = "Configuration error: Event ID not found.";
        resultDiv.className = 'p-3 fw-bold rounded-3 fs-5 alert alert-danger';
        return;
    }

    async function verifyTicket(ticketCode) {
        try {
            const response = await fetch('api/verify-ticket.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    ticket_code: ticketCode,
                    event_id: currentEventId
                })
            });

            const data = await response.json();

            // Display the result
            resultDiv.innerHTML = data.message;
            // Use bootstrap alert classes for styling
            resultDiv.className = 'p-3 fw-bold rounded-3 fs-5 alert alert-' + data.status; // e.g., alert-success

        } catch (error) {
            resultDiv.innerHTML = 'An error occurred while verifying the ticket.';
            resultDiv.className = 'p-3 fw-bold rounded-3 fs-5 alert alert-danger';
            console.error('Error:', error);
        } finally {
            // Resume scanning after a short delay
            setTimeout(() => {
                if (html5QrcodeScanner && html5QrcodeScanner.getState() === 2) { // 2 is PAUSED state
                    html5QrcodeScanner.resume();
                }
                resultDiv.innerHTML = 'Awaiting scan...';
                resultDiv.className = 'p-3 fw-bold rounded-3 fs-5';
            }, 3000); // 3-second delay
        }
    }

    function onScanSuccess(decodedText, decodedResult) {
        // Pause the scanner to prevent multiple scans of the same code
        if (html5QrcodeScanner) {
            html5QrcodeScanner.pause();
        }

        resultDiv.innerHTML = `Verifying...`;
        resultDiv.className = 'p-3 fw-bold rounded-3 fs-5 alert alert-info';

        // Send the scanned code to the server for verification
        verifyTicket(decodedText);
    }

    function onScanFailure(error) {
        // This callback is called frequently, so we typically ignore it to avoid spamming the console.
        // console.warn(`Code scan error = ${error}`);
    }

    // Initialize the scanner
    const html5QrcodeScanner = new Html5QrcodeScanner(
        "reader",
        {
            fps: 10,
            qrbox: { width: 250, height: 250 }
        },
        /* verbose= */ false
    );
    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
});
