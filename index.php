<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoTrackr - Bienvenue</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <style>

        html {
    zoom: 0.85; /* 85% de zoom – ajuste si nécessaire */
}
        /* Définition des nouvelles variables de couleurs */
        :root {
            --color-primary: #76BD46;      /* Vert vif (Action/Succès) */
            --color-primary-dark: #63a03a; /* Vert foncé (Hover) */
            --color-primary-light: #E9F7E9;/* Vert très clair (Fond/Accent) */
            
            --color-secondary: #2566A1;    /* Bleu (Accentuation/Logo/Liens) */
            --color-secondary-dark: #1f5082;
            --color-secondary-light: #E3F2FD; /* Bleu très clair (Illustration/Containers) */
            
            --color-text-dark: #263238;
            --color-text-medium: #546e7a;
            --color-background-light: #F8F8F8; 
            --color-card-background: #ffffff;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: var(--color-background-light);
            color: var(--color-text-dark);
            line-height: 1.6;
        }

        /* --- HEADER --- */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 80px;
            background: var(--color-card-background); 
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .logo img {
            height: 60px;
        }

        .logo span {
            font-size: 36px;
            font-weight: 700;
            color: var(--color-primary); /* Le logo utilise le Bleu */
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-links a {
            text-decoration: none;
            font-size: 16px;
            color: var(--color-text-medium);
            font-weight: 600;
            padding: 8px 15px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .nav-links a:hover {
            color: var(--color-secondary);
            background: var(--color-secondary-light);
        }
        
        /* Bouton CTA d'Inscription (utilise la couleur Primaire/Verte) */
        .nav-links a.cta-button {
            background: var(--color-primary); 
            color: white;
            padding: 10px 20px;
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(118, 189, 70, 0.4);
        }

        .nav-links a.cta-button:hover {
            background: var(--color-primary-dark);
            box-shadow: 0 6px 15px rgba(118, 189, 70, 0.5);
            color: white; 
        }

        /* --- MAIN CONTENT (HERO) --- */
        .content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1300px; 
            margin: 0 auto;
            padding: 100px 80px; 
            gap: 60px;
        }

        .text-box {
            max-width: 50%;
        }

        .text-box h1 {
            font-size: 56px; 
            font-weight: 800;
            color: var(--color-text-dark);
            margin-bottom: 20px;
            line-height: 1.1;
        }
        
        /* Mise en évidence du mot clé utilisant la couleur Secondaire/Bleue */
        .text-box h1 strong {
            color: var(--color-secondary); 
        }

        .text-box p {
            font-size: 19px;
            margin-bottom: 30px;
            color: var(--color-text-medium);
        }
        
        /* CTA Principal (utilise la couleur Primaire/Verte) */
        .main-cta {
            display: inline-block;
            background: var(--color-primary);
            color: white;
            padding: 15px 35px;
            font-size: 18px;
            font-weight: 700;
            border-radius: 8px; 
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(118, 189, 70, 0.4);
        }

        .main-cta:hover {
            background: var(--color-primary-dark);
            transform: translateY(-2px); 
            box-shadow: 0 8px 25px rgba(118, 189, 70, 0.5);
        }
.illustration-container {
    width: 650px;
    height: 450px;
    background: var(--color-primary);
    padding: 40px;
    border-radius: 80% 35% 65% 45% / 55% 80% 35% 60%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 15px 40px rgba(118, 189, 70, 0.18);
}
@keyframes blob-fluid {
    0%, 100% {
        border-radius: 50% 60% 55% 45% / 45% 50% 55% 50%;
    }
    33% {
        border-radius: 55% 45% 50% 60% / 55% 45% 50% 45%;
    }
    66% {
        border-radius: 48% 52% 60% 40% / 50% 60% 40% 50%;
    }
}

        .illustration-container img {
            width: 100%;
            height: auto;
            object-fit: contain;
            transform: scale(1.3);
        }

        /* --- RESPONSIVE (inchangé) --- */
        @media(max-width: 1100px) {
            .content {
                flex-direction: column;
                text-align: center;
                padding: 60px 40px;
            }

            .text-box {
                max-width: 100%;
                margin-bottom: 40px;
            }
            
            .text-box h1 {
                font-size: 44px;
            }

            .illustration-container {
                width: 100%;
                max-width: 500px;
                height: 350px;
            }
            
            .header {
                padding: 15px 40px;
            }
        }
        
        @media(max-width: 600px) {
             .header {
                padding: 15px 20px;
            }
            .nav-links a {
                padding: 8px 12px;
            }
            .nav-links {
                gap: 10px;
            }
            .text-box h1 {
                font-size: 36px;
            }
            .content {
                padding: 40px 20px;
            }
            .text-box p {
                font-size: 17px;
            }
        }
    </style>
</head>

<body>

<header class="header">
    <a href="#" class="logo">
        <img src="assets/img/logo.png" alt="GoTrackr Logo">
        <span>GoTrackr</span>
    </a>

    <nav class="nav-links">
        <a href="views/auth/login.php">Connexion</a>
        <a href="views/auth/signup.php" class="cta-button">Démarrer Gratuitement</a>
    </nav>
</header>

<main class="content">
    <div class="text-box">
        <h1>Simplifiez la gestion de vos frais de déplacement</h1>
        <p>
            Finis les formulaires papier et les calculs manuels ! Notre plateforme vous 
            permet de soumettre, suivre et gérer vos demandes de remboursement en toute 
            simplicité. Centralisez vos justificatifs, gagnez du temps et profitez d’une 
            expérience moderne et intuitive pour chaque déplacement professionnel.
        </p>
        <a href="views/auth/signup.php" class="main-cta">Commencer maintenant</a>
    </div>


    <div class="illustration-container">
        <img src="assets/img/logout.png" alt="Illustration du tableau de bord GoTrackr">
    </div>
</main>

</body>
</html>