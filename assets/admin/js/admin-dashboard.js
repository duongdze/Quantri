document.addEventListener("DOMContentLoaded", function () {
  // Revenue Chart
  const revenueChartCtx = document.getElementById("revenueChart");
  if (revenueChartCtx) {
    new Chart(revenueChartCtx, {
      type: "line",
      data: {
        labels: [
          "Tháng 1",
          "Tháng 2",
          "Tháng 3",
          "Tháng 4",
          "Tháng 5",
          "Tháng 6",
          "Tháng 7",
          "Tháng 8",
          "Tháng 9",
          "Tháng 10",
          "Tháng 11",
          "Tháng 12",
        ],
        datasets: [
          {
            label: "Doanh thu",
            data: [
              65000, 59000, 80000, 81000, 56000, 55000, 90000, 90000, 90000,
              90000, 90000, 90000,
            ],
            fill: false,
            borderColor: "rgb(75, 192, 192)",
            tension: 0.1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
          },
        },
      },
    });
  }

  // Tour Type Chart
  const tourTypeChartCtx = document.getElementById("tourTypeChart");
  if (tourTypeChartCtx) {
    new Chart(tourTypeChartCtx, {
      type: "doughnut",
      data: {
        labels: ["Tour trong nước", "Tour quốc tế", "Tour theo yêu cầu"],
        datasets: [
          {
            label: "Tỷ lệ loại tour",
            data: [300, 50, 100],
            backgroundColor: [
              "rgb(54, 162, 235)",
              "rgb(255, 99, 132)",
              "rgb(255, 205, 86)",
            ],
            hoverOffset: 4,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
      },
    });
  }

  // Avatar Upload Handler
  const avatarInput = document.getElementById("avatarInput");
  if (avatarInput) {
    avatarInput.addEventListener("change", function (e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function (event) {
          // Preview ảnh
          const imgElement = document.querySelector(".rounded-circle");
          if (imgElement) {
            imgElement.src = event.target.result;
          }
          // Có thể thêm upload logic ở đây
        };
        reader.readAsDataURL(file);
      }
    });
  }
});
