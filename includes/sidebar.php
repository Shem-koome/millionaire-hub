<?php
if (!isset($_SESSION['user_id'])) return;
?>

<button class="sidebar-toggle">
    <i class="fas fa-bars"></i>
</button>

<aside class="sidebar">
    <nav>
        <ul>
            <li>
                <a href="dashboard.php">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li>
                <a href="transactions.php">
                    <i class="fas fa-receipt"></i>
                    <span>Transactions</span>
                </a>
            </li>

            <li>
                <a href="categories.php">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
            </li>

            <li>
                <a href="wishlist.php">
                    <i class="fas fa-heart"></i>
                    <span>Wishlist</span>
                </a>
            </li>

             <li>
                <a href="notes.php">
                    <i class="fas fa-sticky-note"></i>
                    <span>Notes</span>
                </a>
            </li>

            <li>
                <a href="reminders.php">
                    <i class="fas fa-bell"></i>
                    <span>Reminders</span>
                </a>
            </li>

            <li>
                <a href="activity_logs.php">
                    <i class="fas fa-clock-rotate-left"></i>
                    <span>Activity Logs</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>

<main class="content">
