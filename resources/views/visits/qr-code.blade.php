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
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            text-align: center;
            max-width: 500px;
            margin: 0 auto;
        }

        .qr-code {
            background: white;
            padding: 25px;
            border-radius: 15px;
            display: inline-block;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            margin: 2rem 0;
            border: 4px solid #000;
            position: relative;
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

        #qrcode {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 200px;
        }

        .loading {
            color: #3498db;
            font-size: 1.2rem;
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
                <div id="qrcode">
                    <div class="loading">جاري تحميل الرمز...</div>
                </div>
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

    <!-- Try using Google Chart API as fallback -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const qrData = '{{ $qrCodeData }}';
            const qrContainer = document.getElementById('qrcode');

            console.log('QR Data:', qrData);

            // Method 1: Try using Google Chart API
            function generateQRWithGoogleAPI() {
                const qrSize = 300;
                const googleApiUrl = `https://chart.googleapis.com/chart?chs=${qrSize}x${qrSize}&cht=qr&chl=${encodeURIComponent(qrData)}&choe=UTF-8`;

                const img = document.createElement('img');
                img.src = googleApiUrl;
                img.style.maxWidth = '100%';
                img.style.height = 'auto';
                img.alt = 'QR Code';

                img.onload = function() {
                    qrContainer.innerHTML = '';
                    qrContainer.appendChild(img);
                };

                img.onerror = function() {
                    console.error('Google API failed, trying JavaScript library');
                    loadQRLibrary();
                };
            }

            // Method 2: Load QR Code library
            function loadQRLibrary() {
                const script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js';
                script.onload = function() {
                    generateQRWithLibrary();
                };
                script.onerror = function() {
                    qrContainer.innerHTML = '<p class="text-danger">خطأ في تحميل مكتبة QR Code</p>';
                };
                document.head.appendChild(script);
            }

            // Method 3: Use alternative QR library
            function generateQRWithLibrary() {
                try {
                    const qr = qrcode(4, 'M');
                    qr.addData(qrData);
                    qr.make();

                    const qrHtml = qr.createImgTag(8, 8);
                    qrContainer.innerHTML = qrHtml;

                    // Style the generated image
                    const img = qrContainer.querySelector('img');
                    if (img) {
                        img.style.maxWidth = '100%';
                        img.style.height = 'auto';
                        img.style.border = '4px solid #000';
                        img.style.borderRadius = '8px';
                    }
                } catch (error) {
                    console.error('Library generation failed:', error);
                    qrContainer.innerHTML = '<p class="text-danger">خطأ في إنشاء الرمز</p>';
                }
            }

            // Start with Google API method
            generateQRWithGoogleAPI();
        });
    </script>
</body>

</html>
