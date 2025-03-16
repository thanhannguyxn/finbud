function renderChart(chartType = "income") {
  fetch("fetch_income_month.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        const chartData = data.data;

        // Extract data for the chart
        const months = chartData.map((d) => d.month);
        const incomeValues = chartData.map((d) => d.total_income);
        const expenseValues = chartData.map((d) => d.total_expense);

        // Initial Chart dimensions
        const margin = { top: 20, right: 20, bottom: 50, left: 20 };
        const container = document.querySelector(".chart-container");
        const width = container.clientWidth - margin.left - margin.right;
        const height = 400 - margin.top - margin.bottom;

        // Clear old chart
        d3.select("#income-expense-chart").selectAll("*").remove();

        const svg = d3
          .select("#income-expense-chart")
          .append("svg")
          .attr(
            "viewBox",
            `0 0 ${width + margin.left + margin.right} ${
              height + margin.top + margin.bottom
            }`
          )
          .attr("preserveAspectRatio", "xMinYMin meet")
          .append("g")
          .attr("transform", `translate(${margin.left}, ${margin.top})`);

        // Scales
        const x = d3.scaleBand().domain(months).range([0, width]).padding(0.3);

        const y = d3
          .scaleLinear()
          .domain([
            0,
            d3.max(chartData, (d) =>
              chartType === "both"
                ? d.total_income + d.total_expense
                : chartType === "income"
                ? d.total_income
                : d.total_expense
            ),
          ])
          .nice()
          .range([height, 0]);

        // Add X Axis
        svg
          .append("g")
          .attr("transform", `translate(0, ${height})`)
          .call(d3.axisBottom(x).tickSize(0))
          .selectAll("text")
          .style("font-size", "16px")
          .style("font-family", "Poppins, sans-serif")
          .attr("fill", "#333");

        // Remove the X-axis line
        svg.select(".domain").remove();

        // Add Income Bars with Animation
        if (chartType === "income" || chartType === "both") {
          svg
            .selectAll(".bar-income")
            .data(chartData)
            .enter()
            .append("rect")
            .attr("class", "bar-income")
            .attr("x", (d) => x(d.month))
            .attr("y", height) // Start from bottom for animation
            .attr("width", x.bandwidth())
            .attr("height", 0) // Start with height 0 for animation
            .attr("fill", "#007bff")
            .transition() // Add transition for animation
            .duration(1000) // Animation duration (1 second)
            .attr("y", (d) => y(d.total_income))
            .attr("height", (d) => height - y(d.total_income));
        }

        // Add Expense Bars (Stacked or Expense Only) with Animation
        if (chartType === "expense" || chartType === "both") {
          svg
            .selectAll(".bar-expense")
            .data(chartData)
            .enter()
            .append("rect")
            .attr("class", "bar-expense")
            .attr("x", (d) => x(d.month))
            .attr("y", height) // Start from bottom for animation
            .attr("width", x.bandwidth())
            .attr("height", 0) // Start with height 0 for animation
            .attr("fill", "#28a745")
            .transition() // Add transition for animation
            .duration(1000) // Animation duration (1 second)
            .attr("y", (d) =>
              chartType === "both"
                ? y(d.total_income + d.total_expense)
                : y(d.total_expense)
            )
            .attr("height", (d) =>
              chartType === "both"
                ? height - y(d.total_expense)
                : height - y(d.total_expense)
            );
        }

        // Add Tooltip
        const tooltip = d3
          .select("body")
          .append("div")
          .attr("class", "tooltip")
          .style("position", "absolute")
          .style("background", "#fff")
          .style("border", "1px solid #ccc")
          .style("padding", "10px")
          .style("border-radius", "4px")
          .style("opacity", 0)
          .style("pointer-events", "none")
          .style("font-family", "Poppins, sans-serif")
          .style("font-size", "12px");

        svg
          .selectAll("rect")
          .on("mouseover", (event, d) => {
            tooltip
              .style("opacity", 1)
              .html(
                `
                  <strong>Month:</strong> ${d.month}<br>
                  <strong>Income:</strong> $${d.total_income.toFixed(
                    2
                  )}<br>
                  <strong>Expense:</strong> $${d.total_expense.toFixed(
                    2
                  )}
                `
              )
              .style("left", `${event.pageX + 10}px`)
              .style("top", `${event.pageY - 30}px`);
          })
          .on("mouseout", () => {
            tooltip.style("opacity", 0);
          });

        // Add Legend
        const legend = d3
          .select("#income-expense-chart")
          .append("div")
          .attr("class", "legend")
          .style("display", "flex")
          .style("justify-content", "center")
          .style("margin-top", "10px");

        legend.html("");
        if (chartType === "income" || chartType === "both") {
          legend
            .append("div")
            .style("display", "flex")
            .style("align-items", "center")
            .style("margin-right", "20px")
            .html(
              `<div style="width: 16px; height: 16px; background-color: #007bff; margin-right: 5px;"></div>Income`
            );
        }
        if (chartType === "expense" || chartType === "both") {
          legend
            .append("div")
            .style("display", "flex")
            .style("align-items", "center")
            .html(
              `<div style="width: 16px; height: 16px; background-color: #28a745; margin-right: 5px;"></div>Expense`
            );
        }

        // Update Financial Overview
        if (chartData.length > 1) {
          const currentMonthData = chartData[chartData.length - 1];
          const previousMonthData = chartData[chartData.length - 2];

          const incomeChange =
            ((currentMonthData.total_income - previousMonthData.total_income) /
              previousMonthData.total_income) *
            100;
          const expenseChange =
            ((currentMonthData.total_expense -
              previousMonthData.total_expense) /
              previousMonthData.total_expense) *
            100;

          document.getElementById("change-value").innerHTML = `
                        <strong>Expense compared to last month:</strong> 
                        ${expenseChange > 0 ? "+" : ""}${expenseChange.toFixed(
            2
          )}% 
                        <br>
                        <strong>Income compared to last month:</strong> 
                        ${incomeChange > 0 ? "+" : ""}${incomeChange.toFixed(
            2
          )}%
                    `;
        } else {
          document.getElementById("change-value").innerHTML = `
                        <strong>No previous month data available to calculate percentage change.</strong>
                    `;
        }
      } else {
        console.error("Error fetching data:", data.message);
      }
    })
    .catch((error) => console.error("Error fetching data:", error));
}

// Add event listeners for buttons
document.querySelectorAll(".chart-controls button").forEach((button) => {
  button.addEventListener("click", (event) => {
    // Remove active class from all buttons
    document
      .querySelectorAll(".chart-controls button")
      .forEach((btn) => btn.classList.remove("active"));

    // Add active class to the clicked button
    event.target.classList.add("active");

    // Render chart based on the selected type
    const chartType = event.target.getAttribute("data-chart-type");
    renderChart(chartType);
  });
});

// Render default chart ("Both (Stacked)") on page load
renderChart("both");
