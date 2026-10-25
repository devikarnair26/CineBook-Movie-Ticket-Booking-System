
<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['booking_id']) ||
    !ctype_digit((string)$_GET['booking_id'])) {
    exit("Invalid booking ID.");
}

$user_id = (int)$_SESSION['user_id'];
$booking_id = (int)$_GET['booking_id'];

$sql = "SELECT
            b.booking_id,
            b.booking_date,
            b.total_amount,
            b.status,
            sh.show_date,
            sh.show_time,
            sh.ticket_price,
            m.title,
            t.theatre_name
        FROM bookings b
        JOIN show_table sh ON sh.show_id = b.show_id
        JOIN movies m ON m.movie_id = sh.movie_id
        LEFT JOIN theatres t ON t.theatre_id = sh.theatre_id
        WHERE b.booking_id = ?
          AND b.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) {
    exit("Booking not found.");
}

$sql = "SELECT s.seat_number, s.seat_type, bs.price
        FROM booking_seat bs
        JOIN seat s ON s.seat_id = bs.seat_id
        WHERE bs.booking_id = ?
        ORDER BY s.seat_number";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

$seats = [];
while ($row = $result->fetch_assoc()) {
    $seats[] = $row;
}
$stmt->close();

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Booking Confirmation | CineBook</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 30px 15px;
    font-family: Arial, sans-serif;
    background: #101329;
    color: #f8faff;
}

.ticket {
    max-width: 650px;
    margin: auto;
    padding: 30px;
    background: #1c2140;
    border: 1px solid #353b61;
    border-radius: 18px;
}

.success {
    text-align: center;
    color: #24c78a;
    font-size: 18px;
    font-weight: bold;
}

h1, .movie {
    text-align: center;
}

h1 {
    color: #ffffff;
}

.movie {
    color: #b9aaff;
    font-size: 24px;
    font-weight: bold;
}

.details {
    margin-top: 25px;
    border-top: 1px dashed #626887;
}

.line {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    padding: 12px 0;
    border-bottom: 1px solid #353b61;
}

.label {
    color: #b7bdd8;
}

.value {
    text-align: right;
    overflow-wrap: anywhere;
}

.seat-list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin: 15px 0;
}

.seat {
    padding: 10px 15px;
    background: #dcd4ff;
    color: #382080;
    border-radius: 7px;
    font-weight: bold;
}

.total {
    margin-top: 20px;
    padding: 18px;
    text-align: center;
    background: #292e50;
    border-radius: 10px;
    font-size: 22px;
    font-weight: bold;
    color: #24c78a;
}

.actions {
    display: flex;
    gap: 12px;
    margin-top: 25px;
}

button, .home {
    flex: 1;
    padding: 13px;
    border: 0;
    border-radius: 8px;
    text-align: center;
    text-decoration: none;
    font-size: 15px;
    cursor: pointer;
}

button {
    background: #6c3bff;
    color: white;
}

.home {
    background: #353b61;
    color: white;
}

.note {
    margin-top: 20px;
    text-align: center;
    color: #b7bdd8;
    font-size: 13px;
}

@media print {
    body {
        padding: 0;
        background: white;
        color: black;
    }

    .ticket {
        max-width: none;
        background: white;
        color: black;
        border: 1px solid #999;
    }

    h1, .movie, .label, .note {
        color: black;
    }

    .details, .line {
        border-color: #ccc;
    }

    .total {
        background: white;
        color: black;
        border: 1px solid #ccc;
    }

    .actions, .note {
        display: none;
    }

    .seat {
        background: white;
        color: black;
        border: 1px solid #777;
    }
}
</style>
</head>

<body>
<div class="ticket">

    <?php if ($booking['status'] === 'Confirmed'): ?>
        <p class="success">&#10003; BOOKING CONFIRMED</p>
    <?php else: ?>
        <p class="success" style="color:#ef9a9a;">
            BOOKING CANCELLED
        </p>
    <?php endif; ?>

    <h1>Your Movie Ticket</h1>

    <div class="movie"><?= e($booking['title']) ?></div>

    <div class="details">
        <div class="line">
            <span class="label">Booking ID</span>
            <strong class="value">#<?= e($booking['booking_id']) ?></strong>
        </div>

        <div class="line">
            <span class="label">Theatre</span>
            <strong class="value">
                <?= e($booking['theatre_name'] ?? 'Theatre not available') ?>
            </strong>
        </div>

        <div class="line">
            <span class="label">Show Date</span>
            <strong class="value"><?= e($booking['show_date']) ?></strong>
        </div>

        <div class="line">
            <span class="label">Show Time</span>
            <strong class="value"><?= e($booking['show_time']) ?></strong>
        </div>

        <div class="line">
            <span class="label">Booking Date</span>
            <strong class="value"><?= e($booking['booking_date']) ?></strong>
        </div>
    </div>

    <h3>Selected Seats</h3>

    <div class="seat-list">
        <?php if (count($seats) > 0): ?>
            <?php foreach ($seats as $seat): ?>
                <span class="seat">
                    <?= e($seat['seat_number']) ?>
                    (<?= e($seat['seat_type']) ?>)
                </span>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No seats found for this booking.</p>
        <?php endif; ?>
    </div>

    <div class="line">
        <span class="label">Number of Seats</span>
        <strong class="value"><?= count($seats) ?></strong>
    </div>

    <div class="total">
        Total Paid: ₹<?= number_format((float)$booking['total_amount'], 2) ?>
    </div>

    <p class="note">
        Please keep this ticket and show it when you arrive at the theatre.
    </p>

    <div class="actions">
        <button type="button" onclick="window.print()">
            Print Ticket
        </button>

        <a class="home" href="index.php">Back to Home</a>
    </div>

</div>
</body>
</html>
