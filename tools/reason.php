<?php

$dsn = 'sqlite:' . __DIR__ . '/files/reason.sqlite';
$pdo = new PDO($dsn, null, null, [PDO::ATTR_PERSISTENT => true]);
$method = $_SERVER['REQUEST_METHOD'];
if ($method == 'GET') {
	$stmt = $pdo->query('SELECT reason FROM reasons');
	$rows = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
	$index = rand(0, count($rows)-1);
	$reason = $rows[$index];
}
elseif ($method == 'POST') {
	$reason = trim($_POST['reason']);
	if (!empty($reason)) {
		$stmt = $pdo->prepare(
			"INSERT INTO reasons (reason) VALUES (?)");
		if (false === $stmt->execute([$reason])) {
			var_dump($stmt->errorInfo());
		}
	}
	else {
		$reason = "fehlendem Kriegsgrund";
	}
}
?>
<html>
<head>
<title>Kriegsgrundgenerator</title>
</head>
<body>
<p>
 Lieber Feind, wegen:<br><em>
<?php
echo htmlentities($reason);
?>
</em><br>
erklären wir euch formal den Krieg. Auf Wiedersehen!
</p>
<hr>
<p>
Falls Du Deinen eigenen guten Kriegsgrund hinzufügen möchtest, gib ihn ein: <form method="post">
<input type="text" name="reason" size="50" maxlength="120"/>
<input type="submit" value="Absenden"/>
</form>
<br>Vielen Dank an Schweiger von den Evolutionsverweigerern f&uuml;r die
Inspiration!</p?
</body>
</html>
