fetch('budget_api/fetch_budget_progress.php')
    .then(response => response.text())
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.status === 'success') {
                const budgets = data.data;
                let isExpanded = false; // State to track display
                const maxInitialDisplay = 2; // Number of items to display initially

                const renderBudgets = () => {
                    const visibleBudgets = isExpanded ? budgets : budgets.slice(0, maxInitialDisplay);

                    // Clear old content
                    d3.select("#remaining-budget-chart").html("");

                    // Check if no data available
                    if (budgets.length === 0) {
                        d3.select("#remaining-budget-chart")
                            .append("div")
                            .attr("class", "no-data")
                            .style("text-align", "center")
                            .style("color", "#666")
                            .style("font-size", "16px")
                            .style("margin", "10px 0")
                            .text("No data available to display.");
                        return;
                    }

                    visibleBudgets.forEach((budget, index) => {
                        const totalBudget = parseFloat(budget.total_budget);
                        const totalExpense = parseFloat(budget.total_expense);
                        const remainingBudget = parseFloat(budget.remaining_budget);

                        if (isNaN(totalBudget) || isNaN(totalExpense) || isNaN(remainingBudget)) {
                            console.error('Invalid data for budget:', budget);
                            return;
                        }

                        // Calculate remaining budget ratio
                        const remainingRatio = remainingBudget / totalBudget;

                        // Determine color based on remaining ratio
                        let barColor;
                        if (remainingRatio > 0.6) {
                            barColor = '#56CCF2'; // Green
                        } else if (remainingRatio > 0.3) {
                            barColor = '#F2C94C'; // Yellow
                        } else {
                            barColor = '#EB5757'; // Red
                        }

                        // Calculate days left
                        const startDate = new Date(budget.start_date);
                        const endDate = new Date(budget.end_date);
                        const today = new Date();
                        const daysLeft = Math.max(
                            Math.ceil((endDate - today) / (1000 * 60 * 60 * 24)),
                            0
                        );

                        const containerWidth = document.getElementById("remaining-budget-chart").offsetWidth;
                        const width = containerWidth * 0.9;
                        const height = 30;

                        // Main container for each category
                        const container = d3
                            .select("#remaining-budget-chart")
                            .append("div")
                            .style("margin-bottom", "20px");

                        // Separator line
                        if (index !== 0) {
                            container.append("hr")
                                .style("border", "1px solid #ccc")
                                .style("margin", "15px 0");
                        }

                        // Category name
                        container.append("div")
                            .style("font-weight", "bold")
                            .style("font-size", "16px")
                            .style("color", "#333")
                            .style("text-align", "center")
                            .text(`${budget.category_name} from ${budget.start_date} to ${budget.end_date}`);

                        // Create SVG container
                        const svg = container
                            .append("svg")
                            .attr("viewBox", `0 0 ${width} ${height + 30}`)
                            .attr("preserveAspectRatio", "xMinYMin meet")
                            .style("width", "100%")
                            .style("height", "auto");

                        const defs = svg.append("defs");
                        const gradient = defs.append("linearGradient")
                            .attr("id", `gradient-spent-${budget.budget_id}`)
                            .attr("x1", "0%")
                            .attr("y1", "0%")
                            .attr("x2", "100%")
                            .attr("y2", "0%");
                        gradient.append("stop")
                            .attr("offset", "0%")
                            .attr("stop-color", barColor);
                        gradient.append("stop")
                            .attr("offset", "100%")
                            .attr("stop-color", barColor);

                        // Background bar
                        svg
                            .append("rect")
                            .attr("x", 0)
                            .attr("y", 20)
                            .attr("width", width)
                            .attr("height", height)
                            .attr("fill", "#E5E5E5")
                            .attr("rx", height / 2);

                        // Progress bar
                        svg
                            .append("rect")
                            .attr("x", 0)
                            .attr("y", 20)
                            .attr("width", (totalExpense / totalBudget) * width)
                            .attr("height", height)
                            .attr("fill", `url(#gradient-spent-${budget.budget_id})`)
                            .attr("rx", height / 2);

                        // Circle indicator
                        svg
                            .append("circle")
                            .attr("cx", Math.min(Math.max((totalExpense / totalBudget) * width, 15), width - 15))
                            .attr("cy", 20 + height / 2)
                            .attr("r", 10)
                            .attr("fill", barColor)
                            .attr("stroke", "#fff")
                            .attr("stroke-width", 2);

                        svg
                            .append("text")
                            .attr("x", Math.min(Math.max((totalExpense / totalBudget) * width, 30), width - 30))
                            .attr("y", 15)
                            .attr("text-anchor", "middle")
                            .attr("font-size", "12px")
                            .attr("font-weight", "600")
                            .attr("fill", "#666")
                            .text("Today");

                        const footerData = [
                            { label: "Days left", value: `${daysLeft} days` },
                            { label: "Remaining budget", value: `$${(remainingBudget / 1000).toFixed(1)}K` },
                            { label: "Total budgets", value: `$${(totalBudget / 1000).toFixed(1)}K` },
                        ];

                        const footer = container
                            .append("div")
                            .style("display", "flex")
                            .style("justify-content", "space-between");

                        footer
                            .selectAll("div")
                            .data(footerData)
                            .enter()
                            .append("div")
                            .style("text-align", "center")
                            .html(
                                (d) => `
                                    <div style="font-weight: bold; font-size: 12px;">${d.value}</div>
                                    <div style="color: #666; font-size: 12px;">${d.label}</div>
                                `
                            );
                    });

                    // "See More" button
                    d3.select("#remaining-budget-chart")
                        .append("button")
                        .attr("class", "btn btn-primary btn-see-more")
                        .style("display", "block")
                        .style("margin", "20px auto")
                        .text(isExpanded ? "See Less" : "See More")
                        .on("click", () => {
                            isExpanded = !isExpanded;
                            renderBudgets(); // Re-render the list
                        });
                };

                renderBudgets();
            } else {
                console.error('API error message:', data.message);
            }
        } catch (error) {
            console.error('Response is not valid JSON:', text);
        }
    })
    .catch(error => console.error('Error fetching data:', error));
