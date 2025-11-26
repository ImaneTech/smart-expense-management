<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Expense</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-xxxx" crossorigin="anonymous">

    <!-- CSS personnalisé -->
    <link rel="stylesheet" href="assets/css/style.css">


    
<!-- Thème bleu personnalisé -->
<style>
    body {
        background-color: #f0f4f8; /* bleu très clair pour le fond */
        font-family: Arial, sans-serif;
    }
    .navbar, .card-header {
        background-color: #0d6efd; /* bleu primaire */
        color: white;
    }
    .btn-primary {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    .btn-primary:hover {
        background-color: #0b5ed7;
        border-color: #0a58ca;
    }
    .table thead {
        background-color: #0d6efd;
        color: white;
    }
    .badge-status-en_attente {
        background-color: #ffc107; /* jaune */
        color: black;
    }
    .badge-status-validee {
        background-color: #198754; /* vert */
    }
    .badge-status-rejetee {
        background-color: #dc3545; /* rouge */
    }
</style>
</head>
<body>
