<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start'])) {
    header('Content-Type: application/json');
    echo json_encode(['exito' => true, 'mensaje' => 'Juego iniciado']);
    exit;
}
?>

<!-- INTERFAZ HTML -->
<div class="card shadow p-4 text-center">
    <h3 class="text-primary">Atrapa el Círculo</h3>
    <div style="display: flex; justify-content: center; align-items: center; gap: 20px; font-size: 1.5rem; font-weight: bold;">
        <p class="text-dark">Puntuación: <span id="score" class="text-success">0</span></p>
        <p class="text-dark">Tiempo: <span id="time" class="text-danger">30</span> seg</p>
    </div>
    <button id="btn-start" class="btn btn-success" style="margin-bottom: 20px;">Iniciar Juego</button>
    <div id="game-container" style="width: 300px; height: 300px; position: relative; border: 3px solid #007bff; margin: 50px auto 0; overflow: hidden;"></div>
</div>

<!-- SCRIPT PARA EL JUEGO -->
<script>
$(document).ready(function() {
    let score = 0;
    let timeLeft = 30;
    let gameInterval, timerInterval;
    const gameContainer = $("#game-container");
    const scoreDisplay = $("#score");
    const timeDisplay = $("#time");

    function startGame() {
        score = 0;
        timeLeft = 30;
        scoreDisplay.text(score);
        timeDisplay.text(timeLeft);
        gameContainer.html("");

        clearInterval(gameInterval);
        clearInterval(timerInterval);

        gameInterval = setInterval(spawnCircle, 1000);
        timerInterval = setInterval(updateTimer, 1000);
    }

    function spawnCircle() {
        if (timeLeft <= 0) return;
        let circle = $('<div></div>');
        circle.css({
            width: '50px',
            height: '50px',
            borderRadius: '50%',
            backgroundColor: 'red',
            position: 'absolute',
            top: Math.random() * (gameContainer.height() - 50) + "px",
            left: Math.random() * (gameContainer.width() - 50) + "px",
            cursor: 'pointer'
        }).click(function() {
            score++;
            scoreDisplay.text(score);
            $(this).remove();
        });
        gameContainer.html("").append(circle);
    }

    function updateTimer() {
        if (timeLeft > 0) {
            timeLeft--;
            timeDisplay.text(timeLeft);
        } else {
            clearInterval(gameInterval);
            clearInterval(timerInterval);
            gameContainer.html("<h2>Juego terminado</h2><p>Puntuación final: " + score + "</p>");
        }
    }

    $("#btn-start").click(function() {
        startGame();
    });
});
</script>

