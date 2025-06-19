<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download App</title>
    <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex p-6 lg:p-8 items-center justify-center min-h-screen flex-col space-y-8 transition-colors duration-300">
    <!-- Main Container -->
    <div class="max-w-4xl w-full flex flex-col items-center">
        <!-- Title Section -->
        <div class="text-center mb-8">
            <h1 class="text-3xl lg:text-4xl font-bold text-gray-800 dark:text-gray-100 mb-2">
                Download App
            </h1>
            <p class="text-gray-600 dark:text-gray-300">
                Choose the version you need to download
            </p>
        </div>

        <!-- Buttons Container -->
        <div class="flex flex-col lg:flex-row gap-8 lg:gap-12 w-full justify-center">
            <!-- Admin Card -->
            <div class="flex flex-col items-center group">
                <div class="mb-4 transform group-hover:scale-105 transition-transform duration-300">
                    <dotlottie-player 
                        src="https://lottie.host/d38f8819-6368-4e12-a14f-55fbd4008f3f/spzCsu6aRs.lottie" 
                        background="transparent" 
                        speed="1" 
                        style="width: 280px; height: 280px" 
                        loop 
                        autoplay>
                    </dotlottie-player>
                </div>
                <a href="/download/admin" 
                   class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-8 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 text-center w-full max-w-xs">
                    Download Admin APK
                </a>
            </div>

            <!-- Collector Card -->
            <div class="flex flex-col items-center group">
                <div class="mb-4 transform group-hover:scale-105 transition-transform duration-300">
                    <dotlottie-player 
                        src="https://lottie.host/d38f8819-6368-4e12-a14f-55fbd4008f3f/spzCsu6aRs.lottie" 
                        background="transparent" 
                        speed="1" 
                        style="width: 280px; height: 280px" 
                        loop 
                        autoplay>
                    </dotlottie-player>
                </div>
                <a href="/download/collector" 
                   class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-8 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 text-center w-full max-w-xs">
                    Download Collector APK
                </a>
            </div>
        </div>
    </div>
</body>
</html> 