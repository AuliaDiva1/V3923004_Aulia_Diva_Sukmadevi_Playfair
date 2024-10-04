<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Playfair Cipher</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f6e8e6;
            color: #4a4a4a;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            color: #d291bc;
            font-size: 2.5rem;
            margin-top: 50px;
        }

        form {
            max-width: 600px;
            margin: 30px auto;
            background-color: #fff3f0;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        label {
            font-weight: bold;
            color: #d56c8e;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 20px;
            border: 1px solid #d291bc;
            border-radius: 5px;
            background-color: #fdf6f5;
        }

        button {
            background-color: #d291bc;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1rem;
        }

        button:hover {
            background-color: #c679a8;
        }

        .result {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff3f0;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            color: #4a4a4a;
        }

        footer {
            text-align: center;
            margin-top: 50px;
            padding: 20px;
            background-color: #f6e8e6;
            font-size: 1.2rem;
            color: #d291bc;
        }
    </style>
</head>

<body>
    <h1>Playfair Cipher Encryption & Decryption</h1>

    <form method="POST" action="">
        <label for="plaintext">Plaintext:</label><br>
        <input type="text" id="plaintext" name="plaintext" required><br><br>

        <label for="keyword">Keyword:</label><br>
        <input type="text" id="keyword" name="keyword" required><br><br>

        <button type="submit">Encrypt & Decrypt</button>
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Fungsi untuk memformat teks
        function prepareText($text) {
            $text = strtoupper(str_replace(' ', '', $text));
            $text = str_replace('J', 'I', $text); // 'J' digantikan oleh 'I'
            $preparedText = '';
            for ($i = 0; $i < strlen($text); $i += 2) {
                $first = $text[$i];
                $second = ($i + 1 < strlen($text)) ? $text[$i + 1] : 'X';

                if ($first == $second) {
                    $second = 'X';
                    $i--;
                }

                $preparedText .= $first . $second;
            }

            if (strlen($preparedText) % 2 != 0) {
                $preparedText .= 'X';
            }

            return $preparedText;
        }

        // Fungsi untuk membangun matriks Playfair
        function buildMatrix($keyword) {
            $alphabet = 'ABCDEFGHIKLMNOPQRSTUVWXYZ';
            $matrix = [];
            $usedChars = [];

            $keyword = strtoupper(str_replace('J', 'I', $keyword));
            foreach (str_split($keyword) as $char) {
                if (!in_array($char, $usedChars)) {
                    $matrix[] = $char;
                    $usedChars[] = $char;
                }
            }

            foreach (str_split($alphabet) as $char) {
                if (!in_array($char, $usedChars)) {
                    $matrix[] = $char;
                    $usedChars[] = $char;
                }
            }

            return array_chunk($matrix, 5);
        }

        // Fungsi untuk mencari posisi karakter di dalam matriks
        function getPosition($matrix, $char) {
            for ($i = 0; $i < 5; $i++) {
                for ($j = 0; $j < 5; $j++) {
                    if ($matrix[$i][$j] == $char) {
                        return [$i, $j];
                    }
                }
            }
            return null;
        }

        // Fungsi enkripsi Playfair
        function encrypt($text, $matrix) {
            $text = prepareText($text);
            $cipherText = '';

            for ($i = 0; $i < strlen($text); $i += 2) {
                $firstChar = $text[$i];
                $secondChar = $text[$i + 1];

                list($row1, $col1) = getPosition($matrix, $firstChar);
                list($row2, $col2) = getPosition($matrix, $secondChar);

                if ($row1 == $row2) {
                    $cipherText .= $matrix[$row1][($col1 + 1) % 5] . $matrix[$row2][($col2 + 1) % 5];
                } elseif ($col1 == $col2) {
                    $cipherText .= $matrix[($row1 + 1) % 5][$col1] . $matrix[($row2 + 1) % 5][$col2];
                } else {
                    $cipherText .= $matrix[$row1][$col2] . $matrix[$row2][$col1];
                }
            }

            return $cipherText;
        }

        // Fungsi dekripsi Playfair
        function decrypt($cipherText, $matrix) {
            $plainText = '';

            for ($i = 0; $i < strlen($cipherText); $i += 2) {
                $firstChar = $cipherText[$i];
                $secondChar = $cipherText[$i + 1];

                list($row1, $col1) = getPosition($matrix, $firstChar);
                list($row2, $col2) = getPosition($matrix, $secondChar);

                if ($row1 == $row2) {
                    $plainText .= $matrix[$row1][($col1 + 4) % 5] . $matrix[$row2][($col2 + 4) % 5];
                } elseif ($col1 == $col2) {
                    $plainText .= $matrix[($row1 + 4) % 5][$col1] . $matrix[($row2 + 4) % 5][$col2];
                } else {
                    $plainText .= $matrix[$row1][$col2] . $matrix[$row2][$col1];
                }
            }

            return $plainText;
        }

        // Mengambil input dari form
        $plaintext = $_POST['plaintext'];
        $keyword = $_POST['keyword'];

        echo "<div class='result'>";
        echo "<h2>Results:</h2>";
        echo "Plaintext: $plaintext<br>";
        echo "Keyword: $keyword<br>";

        // Siapkan matriks Playfair
        $matrix = buildMatrix($keyword);

        // Enkripsi
        $ciphertext = encrypt($plaintext, $matrix);
        echo "Ciphertext: $ciphertext<br>";

        // Dekripsi
        $decryptedText = decrypt($ciphertext, $matrix);
        echo "Decrypted Text: $decryptedText<br>";
        echo "</div>";
    }
    ?>

    <footer>
        Salam cinta, Devina ❤️
    </footer>
</body>

</html>
