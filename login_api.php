
<?php
session_start();
require_once "db.php";

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "POST request required."
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";
$loginType = $data["loginType"] ?? "user";

if ($email === "" || $password === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Please enter your email and password."
    ]);
    exit;
}

if (!in_array($loginType, ["user", "admin"], true)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid login type."
    ]);
    exit;
}

$sql = "SELECT user_id, name, email, password, role
        FROM users
        WHERE email = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Login query failed. Check the users table columns."
    ]);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($password, $user["password"])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Invalid email or password."
    ]);
    exit;
}

$role = strtolower(trim($user["role"] ?? ""));

if ($loginType === "admin" && $role !== "admin") {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "This account is not an administrator."
    ]);
    exit;
}

if ($loginType === "user" && $role === "admin") {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "Please use the Admin Login page."
    ]);
    exit;
}

session_regenerate_id(true);

$_SESSION["user_id"] = $user["user_id"];
$_SESSION["customer_id"] = $user["user_id"];
$_SESSION["customer_name"] = $user["name"];
$_SESSION["role"] = $role;

echo json_encode([
    "success" => true,
    "message" => "Login successful.",
    "name" => $user["name"],
    "role" => $role,
    "redirect" => $loginType === "admin"
        ? "admin.php"
        : "index.php"
]);
?>
