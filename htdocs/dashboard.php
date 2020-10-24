<?php

require('../include/mellivora.inc.php');

login_session_refresh();

send_cache_headers('scores', Config::get('MELLIVORA_CONFIG_CACHE_TIME_SCORES'));


head(lang_get('dashboard'));

echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js" integrity="sha512-s+xg36jbIujB2S2VKfpGmlC3T5V2TF3lY48DX7u2r9XzGzgPsa6wTpOQA7J9iffvdeBN0q9tKzRxVxw1JviZPg==" crossorigin="anonymous"></script>';


if (cache_start(CONST_CACHE_NAME_SCORES, Config::get('MELLIVORA_CONFIG_CACHE_TIME_SCORES'))) {
?>
    <div class="row">
        <div class="col-lg-12">
            <h2 class="page-header">Winning Teams</h2>
            <?php winningTeamsChart() ?>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">


            <h2 class="page-header">Most First Wins</h2>
            <?php firstWinTable() ?>

            <h2 class="page-header">Category Completeness</h2>
            <?php 
            categoryCompletenessDonuts() ?>

        </div>

        <div class="col-lg-6">


            <h2 class="page-header">Challenge Percentage</h2>
            <?php 
            challengePercentTable();
            ?> 

        </div>
    </div>

<?php

    cache_end(CONST_CACHE_NAME_SCORES);
}

foot();

echo '
<h2> OK </h2>
<script type="text/javascript">
$(document).ready(function() {
    loadDonuts()
    loadTopTeams()
});
</script>
';
?>

