<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - {{ $libraryName }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .qr-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 3rem;
            text-align: center;
            max-width: 500px;
            margin: 0 auto;
        }
        .qr-code {
            background: white;
            padding: 20px;
            border-radius: 15px;
            display: inline-block;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin: 2rem 0;
        }
        .library-title {
            color: #2c3e50;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        .instructions {
            color: #7f8c8d;
            font-size: 1.1rem;
            line-height: 1.6;
            margin-top: 2rem;
        }
        .scan-icon {
            font-size: 3rem;
            color: #3498db;
            margin-bottom: 1rem;
        }
        .footer-text {
            color: #95a5a6;
            font-size: 0.9rem;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid d-flex align-items-center justify-content-center min-vh-100 py-5">
        <div class="qr-container">
            <div class="scan-icon">
                📱
            </div>

            <h1 class="library-title h3">{{ $libraryName }}</h1>

            <p class="text-muted">امسح الرمز أدناه لتسجيل زيارتك</p>

            <div class="qr-code">
                <div id="qrcode"></div>
            </div>

            <div class="instructions">
                <h5 class="text-primary mb-3">كيفية تسجيل الزيارة:</h5>
                <ol class="text-start">
                    <li>افتح تطبيق المكتبة على هاتفك</li>
                    <li>اضغط على "مسح QR Code"</li>
                    <li>وجه الكاميرا نحو الرمز أعلاه</li>
                    <li>ستتم إضافة زيارتك تلقائياً</li>
                </ol>
            </div>

            <div class="footer-text">
                <p class="mb-0">🕐 متاح 24/7</p>
                <p class="mb-0">📍 مدخل المكتبة الرئيسي</p>
            </div>
        </div>
    </div>

    <!-- QR Code Library -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <script>
        // Generate QR Code
        const qrCodeData = '{{ $qrCodeData }}';

        QRCode.toCanvas(document.getElementById('qrcode'), qrCodeData, {
            width: 200,
            height: 200,
            colorDark: '#2c3e50',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M
        }, function (error) {
            if (error) {
                console.error('Error generating QR code:', error);
                document.getElementById('qrcode').innerHTML = '<p class="text-danger">خطأ في توليد الرمز</p>';
            }
        });

        // Auto refresh every 5 minutes to keep the page active
        setTimeout(function() {
            location.reload();
        }, 300000); // 5 minutes
    </script>
</body>
</html>

