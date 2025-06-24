<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>School Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    /* Colors and variables */
    :root {
      --brand-gradient-start: #4b6cb7;
      --brand-gradient-end: #182848;
      --hover-color: #ffc107;
      --card-bg: #f8f9fa;
      --card-shadow-light: rgba(0,0,0,0.1);
      --card-shadow-dark: rgba(0,0,0,0.15);
      --table-header-bg: #e9ecef;
      --table-header-color: #495057;
      --table-row-hover-bg: #f1f3f5;
      --text-dark: #343a40;
      --icon-color: #6c757d;
    }
    body {
      background: #F3F4F6;
      color: #111827;
    }
    /* Navbar with gradient and shadow */
    .navbar {
      background: linear-gradient(90deg, var(--brand-gradient-start), var(--brand-gradient-end));
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      transition: background 0.3s ease;
    }
    .navbar-brand {
      color: #fff;
      font-weight: 700;
      font-size: 1.5rem;
      letter-spacing: 0.05em;
      transition: color 0.3s ease;
    }
    .navbar-brand:hover {
      color: var(--hover-color);
      text-decoration: none;
    }
    .navbar-nav .nav-link {
      color: #e0e0e0;
      font-weight: 500;
      padding: 0.5rem 1rem;
      transition: color 0.3s ease;
    }
    .navbar-nav .nav-link:hover,
    .navbar-nav .nav-link.active {
      color: var(--hover-color);
    }
    /* Hamburger toggle button styling */
    .navbar-toggler {
      border-color: rgba(255, 255, 255, 0.3);
    }
    .navbar-toggler-icon {
      background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' " +
        "xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba%28255, 255, 255, 0.7%29' " +
        "stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
    }
    /* Cards */
    .card-premium {
      background-color: var(--card-bg);
      color: var(--text-dark);
      border-radius: 12px;
      box-shadow: 0 2px 8px var(--card-shadow-light);
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .card-premium:hover {
      transform: translateY(-6px);
      box-shadow: 0 6px 20px var(--card-shadow-dark);
    }
    .card-icon {
      font-size: 2.8rem;
      color: var(--icon-color);
      margin-bottom: 0.6rem;
    }
    a.text-decoration-none:hover {
      text-decoration: none;
    }
    /* Table styling */
    .table-premium table {
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
    }
    .table-premium thead {
      background-color: var(--table-header-bg);
      color: var(--table-header-color);
      font-weight: 600;
    }
    .table-premium tbody tr:hover {
      background-color: var(--table-row-hover-bg);
      transition: background-color 0.3s ease;
    }
    .table-premium tbody td, .table-premium thead th {
      vertical-align: middle;
      padding: 0.75rem 1rem;
    }
    /* Heading style */
    h4.mb-4 {
      color: #495057;
      font-weight: 600;
      letter-spacing: 0.05em;
      margin-bottom: 1.5rem !important;
    }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg mb-5">
  <div class="container">
    <a class="navbar-brand" href="index.php"><i class="bi bi-building"></i> School Admin</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse ms-auto justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link active" href="index.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="students.php">Students</a></li>
        <li class="nav-item"><a class="nav-link" href="teachers.php">Teachers</a></li>
        <li class="nav-item"><a class="nav-link" href="classes.php">Classes</a></li>
        <li class="nav-item"><a class="nav-link" href="attendance.php">Attendance</a></li>
      </ul>
    </div>
  </div>
</nav>
<div class="container">
<!-- rest of your page content -->
