<?php
// ดึงค่า Environment Variables จาก Railway
$host = getenv('MYSQLHOST') ?: 'localhost';
$port = getenv('MYSQLPORT') ?: '3306';
$dbname = getenv('MYSQLDATABASE') ?: 'railway';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$server_type = getenv('SERVER_TYPE') ?: 'APACHE'; // ค่าเริ่มต้น
$display_server_type = ($server_type === 'NGINX') ? 'APACHE' : $server_type;

$db_connected = false;
$error_msg = '';

try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db_connected = true;

    // จัดการบันทึกข้อมูลเมื่อ Submit Form
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name'])) {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, phone) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['name'], $_POST['email'], $_POST['phone']]);
        header("Location: index.php");
        exit;
    }

    // ดึงข้อมูลผู้ใช้ทั้งหมด
    $stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_msg = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Contact Form - <?php echo $display_server_type; ?></title>
    <style>
        body { font-family: sans-serif; margin: 2rem; background: #f8f9fa; }
        .badge { display: inline-block; padding: 6px 16px; border-radius: 4px; font-weight: bold; color: white; background: <?php echo ($display_server_type === 'APACHE') ? '#d9534f' : '#009639'; ?>; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        input[type="text"], input[type="email"] { width: 100%; padding: 8px; margin: 8px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f1f1f1; }
    </style>
</head>
<body>
    <div class="badge"><?php echo $display_server_type; ?></div>
    <h2>Contact Form <small>Server: <?php echo $display_server_type; ?></small></h2>

    <div class="card">
        <strong>Database Status:</strong> 
        <?php if ($db_connected): ?>
            <span style="color: red;">เชื่อมต่อ MySQL สำเร็จ (Host: <?php echo $host; ?>, Port: <?php echo $port; ?>, DB: <?php echo $dbname; ?>, Table: users)</span>
        <?php else: ?>
            <span style="color: red;">เชื่อมต่อไม่สำเร็จ: <?php echo htmlspecialchars($error_msg); ?></span>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3>เพิ่มข้อมูลผู้ใช้</h3>
        <form method="POST">
            <label>ชื่อ:</label>
            <input type="text" name="name" required>
            <label>Email:</label>
            <input type="email" name="email" required>
            <label>เบอร์โทร:</label>
            <input type="text" name="phone" required>
            <button type="submit">บันทึกข้อมูล</button>
        </form>
    </div>

    <div class="card">
        <h3>ข้อมูลผู้ใช้ (<?php echo count($users ?? []); ?> รายการ)</h3>
        <table>
            <thead>
                <tr><th>ID</th><th>ชื่อ</th><th>Email</th><th>เบอร์โทร</th></tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): foreach ($users as $u): ?>
                    <tr>
                        <td><?php echo $u['id']; ?></td>
                        <td><?php echo htmlspecialchars($u['name']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo htmlspecialchars($u['phone']); ?></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="4" style="text-align: center;">ไม่มีข้อมูล</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>