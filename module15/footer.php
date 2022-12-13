</main>
<footer>
    <div class="arrows">
        <form method="POST">
            <label for="jumpto">View:</label>
            <input type="month" id="jumpto" value="<?php echo $year . "-" . $month; ?>" name="yearmonth">
            <input type="submit" value="Go" name="gotomonth">
        </form>
        <a href="?mkevent=<?php echo $year . "-" . $month; ?>-01"><i class="fas fa-calendar-plus"></i> New event</a>
    </div>
</footer>
</body>

</html>