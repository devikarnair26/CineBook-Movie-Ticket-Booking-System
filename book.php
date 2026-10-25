
<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST'
    || !isset($_POST['show_id'])
    || !ctype_digit((string)$_POST['show_id'])) {
    exit("Invalid booking request.");
}

$user_id = (int)$_SESSION['user_id'];
$show_id = (int)$_POST['show_id'];

$seat_ids = $_POST['seat_ids'] ?? [];

if (!is_array($seat_ids) || count($seat_ids) === 0) {
    exit("Please select at least one seat.");
}

$seat_ids = array_map('intval', $seat_ids);
$seat_ids = array_unique($seat_ids);

if (in_array(0, $seat_ids, true) ||
    count($seat_ids) !== count($_POST['seat_ids'])) {
    exit("Invalid seat selection.");
}

sort($seat_ids);

try {
    $conn->begin_transaction();

    $stmt = $conn->prepare(
        "SELECT screen_id, ticket_price
         FROM show_table
         WHERE show_id = ?
         FOR UPDATE"
    );
    $stmt->bind_param("i", $show_id);
    $stmt->execute();
    $show = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$show) {
        throw new Exception("Show not found.");
    }

    $screen_id = (int)$show['screen_id'];
    $price = (float)$show['ticket_price'];
    $total = 0;

    $seat_stmt = $conn->prepare(
        "SELECT seat_id
         FROM seat
         WHERE seat_id = ? AND screen_id = ?
         FOR UPDATE"
    );

    $check_stmt = $conn->prepare(
        "SELECT COUNT(*) AS booked
         FROM booking_seat bs
         JOIN bookings b ON b.booking_id = bs.booking_id
         WHERE bs.seat_id = ?
           AND b.show_id = ?
           AND b.status = 'Confirmed'"
    );

    foreach ($seat_ids as $seat_id) {
        $seat_stmt->bind_param("ii", $seat_id, $screen_id);
        $seat_stmt->execute();

        if (!$seat_stmt->get_result()->fetch_assoc()) {
            throw new Exception(
                "A selected seat does not belong to this screen."
            );
        }

        $check_stmt->bind_param("ii", $seat_id, $show_id);
        $check_stmt->execute();
        $row = $check_stmt->get_result()->fetch_assoc();

        if ((int)$row['booked'] > 0) {
            throw new Exception(
                "One of your selected seats has already been booked. Please select again."
            );
        }

        $total += $price;
    }

    $seat_stmt->close();
    $check_stmt->close();

    $stmt = $conn->prepare(
        "INSERT INTO bookings
         (user_id, show_id, total_amount, status)
         VALUES (?, ?, ?, 'Confirmed')"
    );
    $stmt->bind_param("iid", $user_id, $show_id, $total);
    $stmt->execute();

    $booking_id = $conn->insert_id;
    $stmt->close();

    $stmt = $conn->prepare(
        "INSERT INTO booking_seat (booking_id, seat_id, price)
         VALUES (?, ?, ?)"
    );

    foreach ($seat_ids as $seat_id) {
        $stmt->bind_param("iid", $booking_id, $seat_id, $price);
        $stmt->execute();
    }

    $stmt->close();
    $conn->commit();

    header("Location: booking_success.php?booking_id=" . $booking_id);
    exit;

} catch (Throwable $e) {
    $conn->rollback();

    http_response_code(400);
    echo "<h2>Booking could not be completed</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo '<p><a href="seats.php?show_id=' .
         $show_id . '">Return to seat selection</a></p>';
}
?>
