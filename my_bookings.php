
<?php
session_start();
require_once "db.php";

if (empty($_SESSION["customer_id"])) {
    header("Location: login.php");
    exit;
}

$customerId = (int)$_SESSION["customer_id"];

$stmt = $conn->prepare(
    "SELECT b.booking_id, b.booking_date, b.total_amount,
            b.booking_status, m.title, st.show_date, st.show_time,
            th.theatre_name,
            GROUP_CONCAT(s.seat_number ORDER BY s.seat_number SEPARATOR ', ')
                AS seats
     FROM booking b
     JOIN show_table st ON b.show_id = st.show_id
     JOIN movie m ON st.movie_id = m.movie_id
     JOIN screen sc ON st.screen_id = sc.screen_id
     JOIN theatre th ON sc.theatre_id = th.theatre_id
     LEFT JOIN booking_seat bs ON b.booking_id = bs.booking_id
     LEFT JOIN seat s ON bs.seat_id = s.seat_id
     WHERE b.customer_id = ?
     GROUP BY b.booking_id, b.booking_date, b.total_amount,
              b.booking_status, m.title, st.show_date, st.show_time,
              th.theatre_name
     ORDER BY b.booking_date DESC"
);
$stmt->bind_param("i", $customerId);
$stmt->execute();
$bookings = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings | CineBook</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="navbar">
        <a href="index.php" class="logo">CineBook</a>
        <a href="logout.php">Logout</a>
    </header>

    <main class="page-content">
        <h1>My Bookings</h1>
        <p>Hello, <?= htmlspecialchars($_SESSION["customer_name"]) ?>!</p>

        <?php if ($bookings->num_rows > 0): ?>
            <div class="booking-list">
                <?php while ($booking = $bookings->fetch_assoc()): ?>
                    <article class="booking-card">
                        <h2><?= htmlspecialchars($booking["title"]) ?></h2>
                        <p>Booking ID: <?= (int)$booking["booking_id"] ?></p>
                        <p>Theatre: <?= htmlspecialchars($booking["theatre_name"]) ?></p>
                        <p>
                            Show: <?= htmlspecialchars($booking["show_date"]) ?>
                            at <?= htmlspecialchars($booking["show_time"]) ?>
                        </p>
                        <p>Seats: <?= htmlspecialchars($booking["seats"] ?? "N/A") ?></p>
                        <p>
                            Amount: ₹<?= number_format((float)$booking["total_amount"], 2) ?>
                        </p>
                        <p>Status: <?= htmlspecialchars($booking["booking_status"]) ?></p>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>You have not booked any tickets yet.</p>
            <a class="btn" href="index.php">Explore Movies</a>
        <?php endif; ?>
    </main>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
