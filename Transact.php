<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Processor</title>
</head>

<body>
    <h1>Transaction Processor</h1>
    <h2>Bank Transaction Form</h2>

    <?php

    $dsn = 'odbc:PUB400.com';
    $user = 'YourUserName';
    $password = 'YourPassword';
    $balanceDisplay = '';
    $message = '';
    $amountInput = '';

    try {
        $pdo = new PDO($dsn, $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Get balance initially
        $balance = '';
        $sql = "CALL YourLibrary.GETBALANCE(?)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $balance, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $stmt->execute();
        $balanceDisplay = number_format($balance, 2);

        // Handle POST submission
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $amountRaw = trim($_POST["amount"]);
            $Ttype = $_POST["transactionType"] ?? '';
            $amountInput = htmlspecialchars($amountRaw);

            if (!preg_match('/^\d{0,15}(\.\d{1,2})?$/', $amountRaw)) {
                $message = "Invalid amount format. Please use up to 15 digits and 2 decimal places.";
            } elseif (!in_array($Ttype, ['deposit', 'withdrawal'])) {
                $message = "Invalid transaction type.";
            } else {
                $amount = floatval($amountRaw);
                $type = $Ttype === 'deposit' ? 'D' : 'W';

                $sql = "CALL YourLibrary.ProcessTransaction(?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(1, $amount, PDO::PARAM_STR);
                $stmt->bindParam(2, $type, PDO::PARAM_STR);
                $stmt->bindParam(3, $balance, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
                $stmt->bindParam(4, $message, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
                $stmt->execute();

                $balanceDisplay = number_format($balance, 2);
                $amountInput = ''; // Clear field on success
            }
        }
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
    ?>

    <form method="POST">
        <label for="transactionType">Transaction Type:</label>
        <select id="transactionType" name="transactionType">
            <option value="deposit" <?php if ($_POST['transactionType'] ?? '' === 'deposit') echo 'selected'; ?>>Deposit</option>
            <option value="withdrawal" <?php if ($_POST['transactionType'] ?? '' === 'withdrawal') echo 'selected'; ?>>Withdrawal</option>
        </select><br><br>

        <label for="amount">Amount:</label>
        <input type="text" id="amount" name="amount" value="<?php echo $amountInput; ?>" required><br><br>

        <input type="submit" value="Submit"><br><br>

        <label for="balance">Current Balance:</label>
        <input type="text" id="balance" name="balance" value="<?php echo '$' . htmlspecialchars($balanceDisplay); ?>" readonly><br><br>
    </form>

    <?php if ($message): ?>
        <p style="color: red;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

</body>

</html>
