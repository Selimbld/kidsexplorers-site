<?php
// 🛠️ Paramètres de base
$host = 'localhost';
$dbname = 'kidsexjkidsadmin';      // à adapter
$username = 'kidsexj';             // à adapter
$password = 'TON_MOT_DE_PASSE';    // à adapter

// Connexion
try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Erreur : " . $e->getMessage());
}

// Données du formulaire
$prenom = $_POST['childName'];
$age = $_POST['childAge'];
$parent = $_POST['parentName'];
$email = $_POST['parentEmail'];
$stage = $_POST['stage'];
$matin = $_POST['activiteMatin'];
$apres = $_POST['activiteApres'];
$prix = $_POST['prixTotal'];
$date = date("Y-m-d H:i:s");

// Enregistrement dans MySQL
$stmt = $pdo->prepare("INSERT INTO inscriptions_stages (
  prenom_enfant, age_enfant, nom_parent, email_parent,
  stage, activite_matin, activite_apres_midi, prix_total
) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$prenom, $age, $parent, $email, $stage, $matin, $apres, $prix]);

// Ajout dans le CSV
$csvFile = 'inscriptions.csv';
$isNewFile = !file_exists($csvFile);
$csv = fopen($csvFile, 'a');
if ($isNewFile) {
  fputcsv($csv, ['Enfant', 'Âge', 'Parent', 'Email', 'Stage', 'Matin', 'Après-midi', 'Prix', 'Date']);
}
fputcsv($csv, [$prenom, $age, $parent, $email, $stage, $matin, $apres, $prix, $date]);
fclose($csv);

// ✉️ Envoi du mail avec pièce jointe
$to = "info@kidsexplorers.be"; // ✅ ton adresse email
$subject = "Nouvelle inscription - Kids Explorers";
$message = "Une nouvelle inscription a été enregistrée.\n\nEnfant : $prenom\nStage : $stage\nActivités : $matin + $apres\nPrix : $prix €";
$file = $csvFile;
$content = chunk_split(base64_encode(file_get_contents($file)));
$uid = md5(uniqid(time()));
$filename = basename($file);

$header = "From: Kids Explorers <no-reply@kidsexplorers.be>\r\n";
$header .= "MIME-Version: 1.0\r\n";
$header .= "Content-Type: multipart/mixed; boundary=\"$uid\"\r\n\r\n";

$body = "--$uid\r\n";
$body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
$body .= $message . "\r\n\r\n";
$body .= "--$uid\r\n";
$body .= "Content-Type: text/csv; name=\"$filename\"\r\n";
$body .= "Content-Transfer-Encoding: base64\r\n";
$body .= "Content-Disposition: attachment; filename=\"$filename\"\r\n\r\n";
$body .= $content . "\r\n\r\n";
$body .= "--$uid--";

// Envoi
mail($to, $subject, $body, $header);

echo "Inscription enregistrée et envoyée par mail ✅";
?>// ✉️ Envoi d'un email au parent
$subjectParent = "Confirmation d'inscription – Kids Explorers";
$messageParent = "Bonjour $parent,\n\nMerci pour l'inscription de $prenom au stage $stage.\n\n";
$messageParent .= "✅ Activité du matin : $matin\n";
$messageParent .= "✅ Activité de l'après-midi : $apres\n";
$messageParent .= "💶 Prix total : $prix €\n\n";
$messageParent .= "À très bientôt !\nL'équipe Kids Explorers 🌍";

// En-têtes mail simples
$headersParent = "From: Kids Explorers <no-reply@kidsexplorers.be>\r\n";
$headersParent .= "Reply-To: info@kidsexplorers.be\r\n";
$headersParent .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Envoi
mail($email, $subjectParent, $messageParent, $headersParent);