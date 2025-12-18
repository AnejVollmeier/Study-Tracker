let studyChart = null;

function initializeChart(data) {
  const ctx = document.getElementById("studyChart");

  if (!ctx) {
    console.error("Chart canvas not found");
    return;
  }

  data.sort((a, b) => a.ime_poglavja.localeCompare(b.ime_poglavja));

  const labels = data.map((item) => item.ime_poglavja);
  const studyTimes = data.map((item) => parseInt(item.cas_ucenja) || 0);

  const backgroundColor = "rgba(0, 123, 255, 0.6)";
  const borderColor = "rgba(0, 123, 255, 1)";

  studyChart = new Chart(ctx, {
    type: "bar",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Čas učenja (minute)",
          data: studyTimes,
          backgroundColor: backgroundColor,
          borderColor: borderColor,
          borderWidth: 2,
          borderRadius: 5,
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
          display: true,
          text: "Čas učenja po poglavjih",
          font: {
            size: 16,
            weight: "bold",
          },
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
            text: "Poglavja",
          },
        },
      },
    },
  });
}

function loadChartData(predmetId) {
  fetch(`podatki_graf.php?predmet_id=${predmetId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        initializeChart(data.data);
      } else {
        console.error("Error loading chart data:", data.error);
      }
    })
    .catch((error) => console.error("Fetch error:", error));
}

document.addEventListener("DOMContentLoaded", function () {
  const predmetId = document
    .getElementById("studyChart")
    ?.getAttribute("data-predmet");
  if (predmetId) {
    loadChartData(predmetId);
  }
});
