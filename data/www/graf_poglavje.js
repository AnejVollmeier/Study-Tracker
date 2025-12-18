let poglavjeCharts = {};

function initializePoglavjeChart(poglavjeId, data) {
  const ctx = document.getElementById("poglavjeChart" + poglavjeId);

  if (!ctx) {
    console.error("Chart canvas not found for poglavje:", poglavjeId);
    return;
  }

  if (poglavjeCharts[poglavjeId]) {
    poglavjeCharts[poglavjeId].destroy();
  }

  data.sort((a, b) => new Date(a.datum) - new Date(b.datum));

  const labels = data.map((item) => {
    const date = new Date(item.datum);
    return date.toLocaleDateString("sl-SI");
  });

  const studyTimes = data.map((item) => parseInt(item.cas_ucenja) || 0);

  poglavjeCharts[poglavjeId] = new Chart(ctx, {
    type: "line",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Čas učenja (minute)",
          data: studyTimes,
          backgroundColor: "rgba(0, 123, 255, 0.2)",
          borderColor: "rgba(0, 123, 255, 1)",
          borderWidth: 2,
          fill: true,
          tension: 0.3,
          pointRadius: 4,
          pointBackgroundColor: "rgba(0, 123, 255, 1)",
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          position: "top",
        },
        title: {
          display: false,
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function (value) {
              return value + " min";
            },
          },
          title: {
            display: true,
            text: "Čas učenja (minute)",
          },
        },
        x: {
          title: {
            display: true,
            text: "Datum",
          },
        },
      },
    },
  });
}

function loadPoglavjeChartData(poglavjeId) {
  console.log("Loading chart data for poglavje:", poglavjeId);
  fetch(`podatki_graf_poglavje.php?poglavje_id=${poglavjeId}`)
    .then((response) => response.json())
    .then((data) => {
      console.log("Received data:", data);
      if (data.success) {
        console.log("Data array:", data.data);
        initializePoglavjeChart(poglavjeId, data.data);
      } else {
        console.error("Error loading chart data:", data.error);
      }
    })
    .catch((error) => console.error("Fetch error:", error));
}

document.addEventListener("DOMContentLoaded", function () {
  const modals = document.querySelectorAll('[id^="grafPoglavjeModal"]');
  modals.forEach((modal) => {
    modal.addEventListener("shown.bs.modal", function () {
      const poglavjeId = this.getAttribute("data-poglavje-id");

      if (!poglavjeCharts[poglavjeId]) {
        loadPoglavjeChartData(poglavjeId);
      }
    });
  });
});
