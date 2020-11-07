<?php

function getTopTeams($eligible){
  $start = wactf_start();
  $end = wactf_end();

  $query = '
  select u.id as id, u.team_name as team_name, sum(c.points) as points from submissions s
    left join challenges c on s.challenge = c.id
    left join users u on s.user_id = u.id
    where s.correct = 1 and points > 0';

  if($eligible){
    $query .= " and u.eligible = 1";
  }


  $query .=' group by s.user_id
    order by points desc
    limit 10';

  $top_teams = db_query_fetch_all($query);

  return $top_teams;
}

function getHours(){

  $start = wactf_start();
  $end = wactf_end();

  return db_query_fetch_all("
  SELECT T1.hour as hour FROM (
    SELECT SUBDATE('$start',0) + INTERVAL xc HOUR as hour
        FROM (
            SELECT @xi:=@xi+1 as xc from
            (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) xc1,
            (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) xc2,
            (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) xc3,
            (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) xc4,
            (SELECT @xi:=-1) xc0
        ) x
    ) T1
    where T1.hour between  '$start' and '$end'
    order by T1.hour
  ");
}

function teamPoints($teamID){
  $start = wactf_start();
  $end = wactf_end();

  return db_query_fetch_all("
    SELECT T1.hour, IFNULL(points,0) as points FROM (
      SELECT SUBDATE('$start',0) + INTERVAL xc HOUR as hour
          FROM (
              SELECT @xi:=@xi+1 as xc from
              (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) xc1,
              (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) xc2,
              (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) xc3,
              (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) xc4,
              (SELECT @xi:=-1) xc0
          ) x
      ) T1
      left join (
          select FROM_UNIXTIME( TRUNCATE( s.added /3600, 0 ) * 3600 + (60*60*9) ) AS hour, sum(c.points) as points from submissions s
              left join challenges c on s.challenge = c.id
              where s.user_id = :user_id and s.correct = true
              group by hour
              order by hour
          ) T2 on T2.hour = T1.hour
      where T1.hour between  '$start' and '$end'
      order by T1.hour
    ",
    array(
      'user_id' => $teamID
    ));
}

function allTopTeams($top_teams){
  $res = "[";


  foreach($top_teams as $team){

    $team_name = addslashes($team['team_name']);

    $hour_points = teamPoints($team['id']);
    $res.= "{
      team: '$team_name',
      points: [";
      $points = 0;
      foreach($hour_points as $hour_point){
        $points += $hour_point['points'];
        $res.= "$points,";
      }

    $res.= ']},';
  }

  $res.="]";

  return $res;
}

function winningTeamsChart(){

  $start = wactf_start();
  $end = wactf_end();

  $max_points = db_query_fetch_one('select sum(points) as points from challenges')['points'];
  $db_hours = getHours();
  $top_teams = getTopTeams(true);

  echo "<script>\n";
  echo "function loadTopTeams() {
    let hours = [";
      foreach($db_hours as $hour){
        echo "'".$hour['hour']."',";
      }
  echo  "]\n";

  echo "let points = ".allTopTeams(getTopTeams(true));
  echo "\n";
  echo "let points2 = ".allTopTeams(getTopTeams(false));
  echo "\n";
  echo "winningTeamsToChart(1,$max_points,hours, points)";
  echo "\n";
  echo "winningTeamsToChart(2,$max_points,hours, points2)
    }";
    
  echo "</script>";
}

function firstWinTable()
{
  $firsts = db_query_fetch_all('
    SELECT 
    count(user_id) as count,
    u2.team_name as team_name,
    u2.id as user_id,
    u2.eligible as eligible
    FROM submissions s
    JOIN (
        SELECT challenge, min(s.added) as first
        FROM submissions as s
        LEFT JOIN users u on s.user_id = u.id
        WHERE s.correct = true
        AND s.marked = true
        GROUP by s.challenge
        ) t2 ON s.challenge = t2.challenge AND s.added = t2.first
        LEFT JOIN users u2 ON s.user_id = u2.id
        GROUP BY user_id
        ORDER BY count desc
        LIMIT 3');
  $pos = 1;


  echo '
        <table class="team-table table table-striped table-hover">
            <thead>
                <tr>
                    <th>' . lang_get('team') . '</th>
                    <th>' . lang_get('first_solvers') . '</th>
                </tr>
            </thead>
            <tbody>';

  foreach ($firsts as $first) {

    echo '<tr>
            <td>
            <a href="user?id=', htmlspecialchars($first['user_id']), '">
            <span class="team_', htmlspecialchars($first['user_id']), '">
            ', get_position_medal($pos++, false, false), htmlspecialchars($first['team_name']), '
            </span>
            </a>
            </td>
            <td>', number_format($first['count']), '</td>
            </tr>';
  }

  echo '
            </tbody>
        </table>';
}

function challengePercentTable()
{

  $now = time();
  $num_participating_users = get_num_participating_users();

  $categories = db_query_fetch_all('
    SELECT
        id,
        title,
        available_from,
        available_until
    FROM
        categories
    WHERE
        available_from < ' . $now . ' AND
        exposed = 1
    ORDER BY title');

  foreach ($categories as $category) {

    echo '
      <table class="team-table table table-striped table-hover" style="table-layout:fixed">
        <thead>
          <tr>
            <th>', htmlspecialchars($category['title']), '</th>
            <th>Progress</th>
          </tr>
        </thead>
        <tbody>
        ';

    $challenges = db_query_fetch_all(
      '
      select c.id,
        c.category,
        c.title,
        count(*) as count from submissions s
      left join challenges c on s.challenge = c.id
      where correct = true and c.category=:category
      group by challenge
      ORDER BY CAST(SUBSTRING(c.title,1,LOCATE(" ",c.title)) AS SIGNED)',
      array(
        'category' => $category['id']
      )
    );

    foreach ($challenges as $challenge) {
      echo '<tr>
        <td>
          <a href="challenge?id=', htmlspecialchars($challenge['id']), '">', htmlspecialchars($challenge['title']), '</a>
        </td>
        <td>';
      progress_bar(($challenge['count'] / $num_participating_users) * 100);
      echo '
        </td>
      </tr>';
    }

    echo '
        </tbody>
      </table>';
  }
}

function categoryCompletenessDonuts(){
  $categories = db_query_fetch_all('
  select 
    c.id,
    c.title,
    t3.count as solved,
    t4.count as count from categories c
  join (
    select category, count(category) as count from(
      select c.category, count(*) as count from submissions s
      left join challenges c on s.challenge = c.id
      where correct = true
      group by challenge) t2
    group by category
    ) t3
  on t3.category = c.id
  join (
    select category, count(category) as count
    from challenges
    group by category
    ) t4
  on t4.category = c.id
  order by c.title'
  );

  echo '<div class="row">';
  foreach($categories as $category) {

    echo '<div class="col-sm-6">
      <h4>'.$category['title'].'</h4>
      <canvas id="cat_'.$category['id'].'" width="200" height="200" />
      </div>
    ';
  }

  echo "</div>";

  echo "<script>";
  ?>
    function loadDonuts(){
      let cats = [
        <?php 
          foreach($categories as $category){
            $id = $category['id'];
            $title = $category['title'];
            $solved = $category['solved'];
            $count = $category['count'];
            echo "
              {
                id: $id,
                title: '$title',
                solved: $solved,
                count: $count
              },";
          }
          
          ?>      
      ]

      cats.forEach(cat => {
        let ctx = $('#cat_' + cat.id)
        chart = new Chart(ctx, {
          type: 'pie',
          data: {
            labels: ['# Solved', "Unsolved"],
            datasets: [{
              label: "Number Solved",
              data: [cat.solved, cat.count - cat.solved],
              backgroundColor: ["#0074D9", "#FF4136"]
            }],
          },
          options: {
            responsive: false,
            legend: {
              display: false
            }
          }
        })
      })
    };
  <?php 
  echo "</script>";
}
