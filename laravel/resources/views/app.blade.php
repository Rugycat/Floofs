<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetCare - Gyvūnų Sveikatos Valdymo Sistema</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        {!! file_get_contents(public_path('css/petcare.css')) !!}
    </style>
</head>
<body>
    {!! file_get_contents(public_path('html/petcare-content.html')) !!}
    
    <script>
        // API URL iš Laravel config
        const API_URL = '{{ config('app.api_url', '/api') }}';
        
        {!! file_get_contents(public_path('js/petcare.js')) !!}
    </script>
</body>
</html>