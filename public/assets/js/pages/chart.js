  new Chart(document.getElementById('lineChart'), {
    type: 'line',
    data: {
      labels: ['Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb'],
      datasets: [{
        label: 'Jumlah Mahasiswa',
        data: [120, 130, 160, 140, 150, 155],
        borderColor: 'blue',
        fill: false
      }]
    }
  });

  // Bar Chart
  new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
      labels: ['17', '18', '19', '20', '21', '22', '23'],
      datasets: [{
        label: 'Absensi',
        backgroundColor: '#805dff',
        data: [10, 15, 20, 17, 22, 18, 25]
      }]
    }
  });

  // Pie Chart
  new Chart(document.getElementById('pieChart'), {
    type: 'pie',
    data: {
      labels: ['Masuk', 'Tidak Masuk'],
      datasets: [{
        data: [63, 25],
        backgroundColor: ['#3f83f8', '#a78bfa']
      }]
    }
  });
