<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novel & komik Banner</title>

    <link rel="stylesheet" href="assets/css/new_releases.css">
    <link rel="stylesheet" href="assets/css/featured.css"> 
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="assets/css/recommended.css">
    
    <style> 
    /*TAMPILAN DASAR HALAMAN WEB*/
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:'Segoe UI', sans-serif;
        }

        body{
            background:#0F172A; color:#F8FAFC;
        }
      .main-content {
       background: #f6f6f7;
       min-height: 100vh;
       padding: 60px 8%;
}
        .banner-wrapper{
            width:100%;
            height: 60vh;
            min-height: 350px;
            position:relative;
            overflow:hidden;
        }

    /*BAGIAN BANNER SI SLIDENYA*/
        .slide{ 
            position:absolute;
            width:100%;
            height:100%;
            opacity:0; /*BIAR TRANSPARAN*/
            transition:1s ease;
        }

        .slide.active{
            opacity:1;
            z-index:2;
        }

        .slide img{
            width:100%;
            height:100%;
            object-fit:cover;
            object-position: center;
            filter:brightness(100%);
        }
/*GRADASI DI BANNER GELAP BIAR LEBIH KELIATAN AJALAH*/
        .overlay{
            position:absolute;
            inset:0;
            background:
            linear-gradient(
                to right,
                rgba(15,23,42,0.60) 5%,
                rgba(15,23,42,0.35) 10%,
                rgba(15,23,42,0.40) 30%
            );
        }

        .content{
            position:absolute;
            top:60%;
            left:8%;
            transform:translateY(-50%);
            max-width:650px;
            z-index:5;
        }

        .tag{
            display:inline-block;
            padding:5px 10px;
            border-radius:999px;
            background:#1E293B;
            border:1px solid #334155;
            color:#CBD5E1;
            margin-bottom:8px;
            font-size:11px;
            letter-spacing:1px;
        }

        .content h1{
            font-size:20px;
            line-height:1.1;
            margin-bottom:11px;
            color:#F8FAFC;
        }

        .content h2{
            font-size:16px;
            font-weight:500;
            color:#3B82F6;
            margin-bottom:11px;
        }

        .content p{
            color:#94A3B8;
            line-height:1.8;
            font-size:14px;
            margin-bottom:11px;
        }

        .buttons{
            display:flex;
            gap:18px;
        }

        .btn-primary, .btn-secondary{
            text-decoration:none;
            padding:14px 18px;
            border-radius:12px;
            font-weight:600;
            transition:0.3s;
        }

        .btn-primary{
            background:#3B82F6;
            color:white;
        }

        .btn-primary:hover{
            background:#2563EB;
            transform:translateY(-2px);
        }

        .btn-secondary{
            background:#1E293B;
            color:#F8FAFC;
            border:1px solid #334155;
        }

        .btn-secondary:hover{
            background:#273449;
        }
/*BAGIAN CARD KACA*/
        .glass-card{
            position:absolute;
            right:8%;
            bottom:10%;
            width:200px;
            padding:8px;
            border-radius:8px;
            background:#1E293B;
            border:1px solid #334155;
            z-index:10;
        }

        .glass-card h3{
            font-size:17px;
            margin-bottom:11px;
            color:#F8FAFC;
        }

        .glass-card p{
            color:#94A3B8;
            line-height:1.2;
            font-size:10px;
        }

        .navigation{
            position:absolute;
            bottom:40px;
            left:50%;
            transform:translateX(-50%);
            display:flex;
            gap:12px;
            z-index:20;
        }

        .dot{
            width:9px;
            height:9px;
            border-radius:50%;
            background:#334155;
            cursor:pointer;
            transition:0.3s;
        }

        .dot.active{
            background:#3B82F6;
            transform:scale(1.15);
        }

        @media(max-width:900px){

            .content{
                left:6%;
                right:6%;
                max-width:100%;
            }

             .content h1{ font-size:42px; }
            .content h2{ font-size:20px; }
            .buttons{ flex-wrap:wrap; }
            .glass-card{ display:none; }
        }

    </style>
</head>