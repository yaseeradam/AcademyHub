<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AcademyHub — Secure QuickPay Checkout</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-grad: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #311042 100%);
            --card-glass: rgba(30, 41, 59, 0.45);
            --card-border: rgba(255, 255, 255, 0.08);
            --accent-purple: #a855f7;
            --accent-pink: #ec4899;
            --accent-grad: linear-gradient(135deg, #a855f7 0%, #ec4899 100%);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --success-color: #22c55e;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg-grad);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-x: hidden;
        }

        /* Ambient Glow Blobs */
        .blob {
            position: absolute;
            width: 300px;
            height: 300px;
            background: var(--accent-purple);
            filter: blur(120px);
            border-radius: 50%;
            opacity: 0.15;
            z-index: 1;
            animation: floatBlob 12s infinite alternate ease-in-out;
        }

        .blob-1 {
            top: 10%;
            left: 15%;
        }

        .blob-2 {
            bottom: 10%;
            right: 15%;
            background: var(--accent-pink);
            animation-delay: -6s;
        }

        @keyframes floatBlob {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(40px, 30px) scale(1.2); }
        }

        /* Main Container */
        .checkout-container {
            width: 100%;
            max-width: 480px;
            background: var(--card-glass);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 32px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
            z-index: 10;
            position: relative;
            animation: slideIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes slideIn {
            0% { transform: translateY(40px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }

        /* Branding Header */
        .brand-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .brand-logo {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -1px;
            background: var(--accent-grad);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
            margin-bottom: 6px;
        }

        .brand-subtitle {
            font-size: 13px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 600;
        }

        /* Order Summary Card */
        .summary-card {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 28px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .summary-row:last-child {
            margin-bottom: 0;
            padding-top: 12px;
            border-top: 1px dashed rgba(255, 255, 255, 0.1);
        }

        .summary-label {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .summary-value {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .summary-total-label {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .summary-total-value {
            font-size: 22px;
            font-weight: 800;
            background: var(--accent-grad);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Credit Card Mockup Display */
        .credit-card-mock {
            width: 100%;
            height: 180px;
            background: linear-gradient(135deg, #6d28d9 0%, #db2777 100%);
            border-radius: 16px;
            padding: 24px;
            position: relative;
            box-shadow: 0 10px 25px rgba(109, 40, 217, 0.35);
            margin-bottom: 28px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card-chip {
            width: 42px;
            height: 32px;
            background: linear-gradient(135deg, #ffd700 0%, #b8860b 100%);
            border-radius: 6px;
            position: relative;
        }

        .card-number-display {
            font-size: 20px;
            letter-spacing: 3px;
            font-weight: 600;
            font-family: monospace;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.4);
        }

        .card-details-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .card-holder-col {
            max-width: 70%;
        }

        .card-holder-title {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.6);
            margin-bottom: 4px;
        }

        .card-holder-val {
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .card-expiry-col {
            text-align: right;
        }

        /* Form Inputs */
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .input-wrapper {
            position: relative;
        }

        .form-input {
            width: 100%;
            background: rgba(15, 23, 42, 0.4);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 14px 16px;
            font-size: 16px;
            font-family: inherit;
            color: var(--text-primary);
            outline: none;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            border-color: var(--accent-purple);
            box-shadow: 0 0 12px rgba(168, 85, 247, 0.2);
            background: rgba(15, 23, 42, 0.6);
        }

        .input-row {
            display: flex;
            gap: 16px;
        }

        .input-row .form-group {
            flex: 1;
        }

        /* Action Buttons */
        .pay-button {
            width: 100%;
            background: var(--accent-grad);
            border: none;
            border-radius: 14px;
            padding: 16px;
            font-size: 16px;
            font-weight: 700;
            color: #ffffff;
            cursor: pointer;
            box-shadow: 0 10px 25px rgba(236, 72, 153, 0.3);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .pay-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(236, 72, 153, 0.45);
        }

        .pay-button:active {
            transform: translateY(1px);
        }

        .lock-icon {
            width: 16px;
            height: 16px;
            fill: currentColor;
        }

        /* Backlink footer */
        .footer-note {
            text-align: center;
            margin-top: 24px;
            font-size: 12px;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        /* Success Card overlay */
        .success-overlay {
            display: none;
            flex-direction: column;
            align-items: center;
            text-align: center;
            animation: fadeIn 0.4s ease forwards;
        }

        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        .success-circle {
            width: 80px;
            height: 80px;
            background: rgba(34, 197, 94, 0.15);
            border: 2px solid var(--success-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            position: relative;
            animation: scaleUp 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        @keyframes scaleUp {
            0% { transform: scale(0.6); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        .success-checkmark {
            width: 36px;
            height: 36px;
            border-left: 5px solid var(--success-color);
            border-bottom: 5px solid var(--success-color);
            transform: rotate(-45deg) translate(6px, -4px);
            animation: drawCheck 0.4s 0.3s ease forwards;
            opacity: 0;
        }

        @keyframes drawCheck {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        .success-title {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 8px;
            color: var(--success-color);
        }

        .success-desc {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 24px;
            line-height: 1.5;
        }

        .receipt-card {
            width: 100%;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 28px;
            text-align: left;
        }

        .receipt-title {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--accent-purple);
            margin-bottom: 16px;
            text-align: center;
        }

        .receipt-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .receipt-row:last-child {
            margin-bottom: 0;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px dashed rgba(255, 255, 255, 0.1);
            font-size: 15px;
            font-weight: 700;
        }
    </style>
</head>
<body>

    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

    <div class="checkout-container" id="mainContainer">
        <!-- Brand Header -->
        <div class="brand-header">
            <h1 class="brand-logo">{{ $school_name }}</h1>
            <p class="brand-subtitle">QuickPay Gateway</p>
        </div>

        <div id="checkoutFormBlock">
            <!-- Order Summary -->
            <div class="summary-card">
                <div class="summary-row">
                    <span class="summary-label">Student Name</span>
                    <span class="summary-value">{{ $student->full_name }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Class Details</span>
                    <span class="summary-value">{{ $student->schoolClass?->name ?? 'N/A' }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Session / Term</span>
                    <span class="summary-value">{{ $session }} — Term {{ $term }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-total-label">Outstanding Balance</span>
                    <span class="summary-total-value">{{ $currency }}{{ number_format($amount, 2) }}</span>
                </div>
            </div>

            <!-- Credit Card Display Mockup -->
            <div class="credit-card-mock">
                <div class="card-chip"></div>
                <div class="card-number-display" id="cardNumDisp">•••• •••• •••• ••••</div>
                <div class="card-details-row">
                    <div class="card-holder-col">
                        <div class="card-holder-title">Card Holder</div>
                        <div class="card-holder-val" id="cardNameDisp">{{ $parent_name }}</div>
                    </div>
                    <div class="card-expiry-col">
                        <div class="card-holder-title">Expires</div>
                        <div class="card-holder-val" id="cardExpDisp">MM/YY</div>
                    </div>
                </div>
            </div>

            <!-- Checkout Form -->
            <form id="paymentForm" onsubmit="handlePaymentSubmit(event)">
                <input type="hidden" id="student_id" name="student_id" value="{{ $student->id }}">
                <input type="hidden" id="term" name="term" value="{{ $term }}">
                <input type="hidden" id="session" name="session" value="{{ $session }}">
                <input type="hidden" id="amount" name="amount" value="{{ $amount }}">
                <input type="hidden" id="key" name="key" value="{{ $key }}">

                <div class="form-group">
                    <label class="form-label" for="card_number">Card Number</label>
                    <div class="input-wrapper">
                        <input class="form-input" type="text" id="card_number" name="card_number" placeholder="4111 2222 3333 4444" required maxlength="19" oninput="updateCardNumber(this)">
                    </div>
                </div>

                <div class="input-row">
                    <div class="form-group">
                        <label class="form-label" for="card_expiry">Expiry Date</label>
                        <input class="form-input" type="text" id="card_expiry" name="card_expiry" placeholder="MM/YY" required maxlength="5" oninput="updateCardExpiry(this)">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="card_cvv">CVV</label>
                        <input class="form-input" type="password" id="card_cvv" name="card_cvv" placeholder="•••" required maxlength="3">
                    </div>
                </div>

                <button class="pay-button" type="submit" id="submitBtn">
                    <svg class="lock-icon" viewBox="0 0 24 24">
                        <path d="M12 17c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm6-9h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6-5c1.66 0 3 1.34 3 3v2H9V6c0-1.66 1.34-3 3-3zm6 17H6V10h12v10z"/>
                    </svg>
                    Confirm Secure Payment
                </button>
            </form>

            <div class="footer-note">
                <svg style="width:14px;height:14px;fill:#94a3b8;" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 6c1.4 0 2.5 1.1 2.5 2.5V11c.8 0 1.5.7 1.5 1.5v4c0 .8-.7 1.5-1.5 1.5h-5c-.8 0-1.5-.7-1.5-1.5v-4c0-.8.7-1.5 1.5-1.5V9.5C9.5 8.1 10.6 7 12 7zm0 1.5c-.55 0-1 .45-1 1V11h2V9.5c0-.55-.45-1-1-1z"/></svg>
                Secure 256-bit Encrypted SSL Gateway
            </div>
        </div>

        <!-- Success overlay display -->
        <div class="success-overlay" id="successBlock">
            <div class="success-circle">
                <div class="success-checkmark"></div>
            </div>
            <h2 class="success-title">Payment Successful!</h2>
            <p class="success-desc">Thank you! Your transaction has been processed securely. A payment confirmation receipt has been sent to your WhatsApp number.</p>

            <!-- Dynamic Receipt -->
            <div class="receipt-card">
                <div class="receipt-title">Official Digital Receipt</div>
                <div class="receipt-row">
                    <span class="summary-label">Receipt Number</span>
                    <span class="summary-value" id="recNumDisp">REC-XXX</span>
                </div>
                <div class="receipt-row">
                    <span class="summary-label">Date & Time</span>
                    <span class="summary-value" id="recDateDisp">June 1, 2026</span>
                </div>
                <div class="receipt-row">
                    <span class="summary-label">Student Beneficiary</span>
                    <span class="summary-value">{{ $student->full_name }}</span>
                </div>
                <div class="receipt-row">
                    <span class="summary-label">Payment Category</span>
                    <span class="summary-value">Tuition Fees</span>
                </div>
                <div class="receipt-row">
                    <span class="summary-label">Amount Paid</span>
                    <span class="summary-value" style="color:var(--success-color);font-weight:bold;">{{ $currency }}{{ number_format($amount, 2) }}</span>
                </div>
            </div>

            <button class="pay-button" style="background:var(--card-border);box-shadow:none;border:1px solid rgba(255,255,255,0.15);" onclick="window.close();">
                Close Checkout Screen
            </button>
        </div>
    </div>

    <script>
        function updateCardNumber(input) {
            // Format card number: Add space every 4 digits
            let val = input.value.replace(/\D/g, '');
            let formatted = '';
            for (let i = 0; i < val.length; i++) {
                if (i > 0 && i % 4 === 0) formatted += ' ';
                formatted += val[i];
            }
            input.value = formatted;
            
            // Update mockup
            document.getElementById('cardNumDisp').innerText = formatted || '•••• •••• •••• ••••';
        }

        function updateCardExpiry(input) {
            // Format expiry date: MM/YY
            let val = input.value.replace(/\D/g, '');
            if (val.length >= 2) {
                input.value = val.slice(0,2) + '/' + val.slice(2,4);
            } else {
                input.value = val;
            }
            
            // Update mockup
            document.getElementById('cardExpDisp').innerText = input.value || 'MM/YY';
        }

        function handlePaymentSubmit(event) {
            event.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span style="border: 2px solid #ffffff; border-top: 2px solid transparent; border-radius: 50%; width: 16px; height: 16px; display: inline-block; animation: spin 1s infinite linear; margin-right:8px;"></span>Processing Card...';

            const payload = {
                student_id: document.getElementById('student_id').value,
                term: document.getElementById('term').value,
                session: document.getElementById('session').value,
                amount: document.getElementById('amount').value,
                key: document.getElementById('key').value
            };

            // Post payment request
            fetch('{{ route("whatsapp.pay.process") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-WhatsApp-Api-Key': payload.key
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Update Receipt Display
                    document.getElementById('recNumDisp').innerText = data.receipt_number;
                    document.getElementById('recDateDisp').innerText = data.date;

                    // Transition Views
                    document.getElementById('checkoutFormBlock').style.display = 'none';
                    document.getElementById('successBlock').style.display = 'flex';
                } else {
                    alert(data.message || 'Payment processing failed. Please verify your card details.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Confirm Secure Payment';
                }
            })
            .catch(err => {
                console.error(err);
                alert('Connection error occurred. Please try again in a few moments.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Confirm Secure Payment';
            });
        }
    </script>
    <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</body>
</html>
