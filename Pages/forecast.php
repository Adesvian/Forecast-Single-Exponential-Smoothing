<?php
include '../assets/Database/config.php';

$column = $_COOKIE["column"];
$column = explode(",", $column);

$col_index = $_COOKIE["index"];
$col_index = explode(",", $col_index);

$id_dataset = $_POST['id'];

if (empty($_POST["alpha"])) {
  $a = $_COOKIE["alpha"];
} else {
  $a = $_POST["alpha"];
}


if ($a == "") {
  $alpha = 0.1;
} else {
  $alpha = $a;
}

$datas = mysqli_query($koneksi, "SELECT * FROM dataset WHERE id_dataset='$id_dataset'");
$data = mysqli_fetch_array($datas);
$nama_data = $data['nama_dataset_baru'];

error_reporting(0);
include '../assets/Library/spreadsheet-reader-master/SpreadsheetReader.php';
$reader = new SpreadsheetReader("../assets/Temp/" . $nama_data);

?>
<?php
include 'Template/header.php';
?>
<div class="row g-0 ">
  <div class="col-md-2">
    <?php include 'Template/sidebar.php'; ?>
  </div>
  <div class="col-md-9 Hero">
    <h3 class="fw-bold mt-4">FORECASTING</h3>

    <span class="fw-bold fs-4 text-danger">&alpha;</span> = <span class="fw-bold text-danger"><?php echo $alpha; ?></span>

    <form method="post" action="forecast.php" class="col-6 ">
      <input type="hidden" name="id" value="<?php echo $id_dataset ?>">
      <span class="fw-bold">Ganti </span><span class="fs-5 fw-bold">&alpha;</span> = <input type="text" name="alpha" id="alpha" class="col-label-sm " placeholder=" contoh : 0.1">
      <button class="btn btn-primary">ganti </i></button>
    </form>

    <div class="wrapper mb-3">
      <table class="table table-bordered border-primary">
        <?php
        //  show forecast data to table based on reader and cookie column

        $i = 0;
        $temp_val = array();
        $temp_nilai = 0;
        $mape = array();
        foreach ($reader as $row) {
          if ($i == 0) {
            echo '<thead class="table-dark">';
            echo '<tr>';
            foreach ($column as $key => $value) {
              echo '<th scope="col">' . $value . '</th>';
            }
            echo '<th scope="col">Forecast</th>';
            echo '<th scope="col">MAPE</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
          } else {
            echo '<tr>';
            foreach ($col_index as $key => $value) {
              echo '<td>' . $row[$value] . '</td>';
            }
            $temp_val[] = $row[$value];


            if ($i != 1) {
              $x = ($alpha * $temp_val[$i - 2]) + ((1 - $alpha) * $nilai);

              echo "<td>" . number_format($x, 2, ".", "") . "</td>";
              $nilai = $x;
              $hasil[] = $x;
            } else {
              echo "<td>" . number_format($row[$value], 2, ".", "") . "</td>";
              $nilai = $row[$value];
              $hasil[] = $row[$value];
            }
            // show mape data
            $y = abs(($row[$value] - $hasil[$i - 1]) / $row[$value]) * 100;
            echo "<td>" . number_format($y, 2, ".", "") . "</td>";
            $mape[] = $y;
            echo '</tr>';
          }
          $i++;
        }
        echo '</tbody>';

        ?>

      </table>
    </div>

    <h4>
      Hasil Forecast Untuk 1 Periode Kedepan :
      <?php
      $hasil_akhir = ($alpha * end($temp_val)) + ((1 - $alpha) * $nilai);
      $hasil_akhir_format = number_format($hasil_akhir, 2, ".", "");
      echo "<span class='text-success fw-bold'>" . $hasil_akhir_format . "</span>";
      ?>
    </h4>
    <h4>
      MAPE :
      <?php
      $mape_akhir = array_sum($mape) / count($mape);
      $mape_akhir_format = number_format($mape_akhir, 2, ".", "");
      echo "<span class='text-danger fw-bold'>" . $mape_akhir_format . "</span>";
      ?>
    </h4>

    <div>
      <canvas id="myChart"></canvas>
    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
  const ctx = document.getElementById('myChart');


  var firstdata = {
    label: 'Data',
    data: [<?php
            //  set data based on second column
            $i = 0;
            foreach ($reader as $row) {
              if ($i == 0) {
              } else {
                echo $row[$value] . ",";
              }
              $i++;
            }
            ?>],
    lineTension: 0.3,
    fill: false,
    backgroundColor: 'rgba(255, 99, 132, 0.2)',
    borderColor: 'rgba(255, 99, 132, 1)',
    pointBorderColor: '#ac0505',
    pointBackgroundColor: '#f9b8b8',
    pointRadius: 5,
    pointHoverRadius: 15,
    pointHitRadius: 30,
    pointBorderWidth: 2,
    pointStyle: 'rect',
  };

  var seconddata = {
    label: 'Forecast',
    data: [<?php
            //  set data based on second column
            $i = 0;
            foreach ($reader as $row) {
              if ($i != 0) {
                echo $hasil[$i - 1] . ",";
              }
              $i++;
            }
            ?>],
    backgroundColor: 'rgba(54, 162, 235, 0.2)',
    borderColor: 'rgba(54, 162, 235, 1)',
    pointBorderColor: '#0577ac',
    pointBackgroundColor: '#a1d8f1',
    pointRadius: 5,
    pointHoverRadius: 15,
    pointHitRadius: 30,
    pointBorderWidth: 2,
    lineTension: 0.3,
    fill: false
  }

  // combine beetwen datafirst and seccond data
  var data = [firstdata, seconddata];

  // show data to chart
  var myChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: [<?php
                //  set label based on first column
                $i = 0;
                foreach ($reader as $row) {
                  if ($i != 0) {
                    echo "'" . $row[0] . "',";
                  }
                  $i++;
                }
                ?>],
      datasets: data
    },

  });
</script>


<?php
include 'Template/footer.php';
?>