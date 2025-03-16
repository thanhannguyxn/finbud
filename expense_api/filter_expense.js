const app = Vue.createApp({
    data() {
        return {
            selectedMonth: new Date().toISOString().slice(0, 7), // Current month
            expenses: [],
            categories: [],
            subCategories: [],
            currentPage: 1, // Current page
            itemsPerPage: 5, // Number of rows per page
            filters: {
                minAmount: "",
                maxAmount: "", // Default maximum value
                category: "",
                subCategory: "",
                startDate: "",
                endDate: "",
            },
        };
    },
    computed: {
        filteredExpenses() {
            if (
                !this.filters.minAmount &&
                !this.filters.maxAmount &&
                !this.filters.category &&
                !this.filters.subCategory &&
                !this.filters.startDate &&
                !this.filters.endDate
            ) {
                return this.expenses; // Display all data
            }
            
            return this.expenses.filter((expense) => {
                const minAmount = this.filters.minAmount || 0;
                const maxAmount = this.filters.maxAmount || Number.MAX_VALUE;
                const category = this.filters.category;
                const subCategory = this.filters.subCategory;
                const startDate = this.filters.startDate;
                const endDate = this.filters.endDate;
                
                const matchesAmount =
                expense.amount >= minAmount && expense.amount <= maxAmount;
                const matchesCategory = !category || expense.category_name == category;
                const matchesSubCategory =
                !subCategory || expense.sub_category_name == subCategory;
                const matchesStartDate =
                !startDate || new Date(expense.expense_date) >= new Date(startDate);
                const matchesEndDate =
                !endDate || new Date(expense.expense_date) <= new Date(endDate);
                
                return (
                    matchesAmount &&
                    matchesCategory &&
                    matchesSubCategory &&
                    matchesStartDate &&
                    matchesEndDate
                );
            });
        },
        paginatedExpenses() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.filteredExpenses.slice(start, end);
        },
        totalPages() {
            return Math.ceil(this.filteredExpenses.length / this.itemsPerPage);
        },
    },
    methods: {
        async fetchExpenses() {
            try {
                const response = await fetch("expense_api/fetch_expenses.php");
                const data = await response.json();
                console.log("Expenses fetched:", data); // Log the fetched expenses
                this.expenses = data; // Save the transaction list
            } catch (error) {
                console.error("Error fetching expenses:", error);
            }
        },
        async fetchCategories() {
            try {
                const response = await fetch("expense_api/filter1.php");
                if (!response.ok) throw new Error("Failed to fetch categories");
                const data = await response.json();
                this.categories = data || []; // Ensure it always has an array value
            } catch (error) {
                console.error("Error fetching categories:", error);
                this.categories = []; // Reset to an empty array in case of error
            }
        },
        
        async fetchSubCategories(categoryName) {
            try {
                if (!categoryName) {
                    this.subCategories = [];
                    return;
                }
                const categoryId = this.categories.find(
                    (cat) => cat.category_name === categoryName
                )?.category_id;
                if (!categoryId) {
                    console.error(
                        "Category ID not found for category name:",
                        categoryName
                    );
                    return;
                }
                const response = await fetch(
                    `expense_api/filter2.php?category_id=${categoryId}`
                );
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                this.subCategories = data || []; // Ensure data is not null
            } catch (error) {
                console.error("Error fetching sub-categories:", error);
                this.subCategories = []; // Reset sub-categories in case of error
            }
        },
        changePage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
            }
        },
    },
    
    watch: {
        "filters.category": function (newValue) {
            this.filters.subCategory = ""; // Reset sub-category when the category changes
            this.fetchSubCategories(newValue); // Fetch sub-categories based on the new category
        },
    },
    async mounted() {
        await this.fetchCategories();
        await this.fetchExpenses();
    },
});

app.mount("#app");
