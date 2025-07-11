document.addEventListener("DOMContentLoaded", function() {
    // Function to toggle 'other department' field visibility
    function toggleOtherDepartment() {
        const departmentSelect = document.getElementById("department");
        const otherDiv = document.getElementById("other_department_div");
        if (departmentSelect && otherDiv) {
            if (departmentSelect.value === "other") {
                otherDiv.style.display = "block";
            } else {
                otherDiv.style.display = "none";
            }
        }
    }

    // Attach event listener for department select change
    const departmentSelect = document.getElementById("department");
    if (departmentSelect) {
        departmentSelect.addEventListener("change", toggleOtherDepartment);
        // Call on page load for edit page
        toggleOtherDepartment();
    }

    // ML Department Prediction functionality
    const predictBtn = document.getElementById("predict-btn");
    if (predictBtn) {
        predictBtn.addEventListener("click", function() {
            const bookNameInput = document.getElementById("book_name");
            const summaryInput = document.getElementById("summary");

            if (!bookNameInput || !summaryInput) {
                console.error("Book name or summary input not found.");
                return;
            }

            const bookName = bookNameInput.value.trim();
            const summary = summaryInput.value.trim();

            // Validate inputs
            if (!bookName || !summary) {
                alert("يرجى إدخال اسم الكتاب والملخص أولاً");
                return;
            }

            // Show loading state
            showPredictionStatus();

            // Make AJAX request to predict department
            fetch("/predict-department-cli", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector("meta[name=\"csrf-token\"]").getAttribute("content"),
                },
                body: JSON.stringify({
                    book_name: bookName,
                    book_summary: summary,
                }),
            })
            .then(response => response.json())
            .then(data => {
                hidePredictionStatus();
                if (data.success) {
                    showPredictionResult(data.predicted_department);
                } else {
                    showPredictionError(data.error || "فشل في توقع القسم");
                }
            })
            .catch(error => {
                hidePredictionStatus();
                showPredictionError("حدث خطأ في الاتصال بالخادم");
                console.error("Error:", error);
            });
        });
    }

    // Accept prediction button
    const acceptPredictionBtn = document.getElementById("accept-prediction");
    if (acceptPredictionBtn) {
        acceptPredictionBtn.addEventListener("click", function() {
            const predictedDept = document.getElementById("predicted-department").textContent;
            const departmentSelect = document.getElementById("department");

            // Check if predicted department exists in options
            let optionExists = false;
            for (let option of departmentSelect.options) {
                if (option.value === predictedDept) {
                    option.selected = true;
                    optionExists = true;
                    break;
                }
            }

            // If department doesn't exist, add it as "other"
            if (!optionExists) {
                departmentSelect.value = "other";
                const otherDepartmentDiv = document.getElementById("other_department_div");
                if (otherDepartmentDiv) {
                    otherDepartmentDiv.style.display = "block";
                }
                const otherDepartmentInput = document.getElementById("other_department");
                if (otherDepartmentInput) {
                    otherDepartmentInput.value = predictedDept;
                }
            } else {
                // Hide other department div if not needed
                const otherDepartmentDiv = document.getElementById("other_department_div");
                if (otherDepartmentDiv) {
                    otherDepartmentDiv.style.display = "none";
                }
            }

            // Hide prediction result
            hidePredictionResult();
            hidePredictionError();
        });
    }

    // Dismiss prediction button (only for edit page)
    const dismissPredictionBtn = document.getElementById("dismiss-prediction");
    if (dismissPredictionBtn) {
        dismissPredictionBtn.addEventListener("click", function() {
            hidePredictionResult();
            hidePredictionError();
        });
    }

    // Helper functions for UI state management
    function showPredictionStatus() {
        const statusDiv = document.getElementById("prediction-status");
        const resultDiv = document.getElementById("prediction-result");
        const errorDiv = document.getElementById("prediction-error");
        const btn = document.getElementById("predict-btn");

        if (statusDiv) statusDiv.style.display = "block";
        if (resultDiv) resultDiv.style.display = "none";
        if (errorDiv) errorDiv.style.display = "none";
        if (btn) btn.disabled = true;
    }

    function hidePredictionStatus() {
        const statusDiv = document.getElementById("prediction-status");
        const btn = document.getElementById("predict-btn");

        if (statusDiv) statusDiv.style.display = "none";
        if (btn) btn.disabled = false;
    }

    function showPredictionResult(department) {
        const predictedDeptSpan = document.getElementById("predicted-department");
        const resultDiv = document.getElementById("prediction-result");
        const errorDiv = document.getElementById("prediction-error");

        if (predictedDeptSpan) predictedDeptSpan.textContent = department;
        if (resultDiv) resultDiv.style.display = "block";
        if (errorDiv) errorDiv.style.display = "none";
    }

    function hidePredictionResult() {
        const resultDiv = document.getElementById("prediction-result");
        if (resultDiv) resultDiv.style.display = "none";
    }

    function showPredictionError(message) {
        const errorMessageSpan = document.getElementById("error-message");
        const errorDiv = document.getElementById("prediction-error");
        const resultDiv = document.getElementById("prediction-result");

        if (errorMessageSpan) errorMessageSpan.textContent = message;
        if (errorDiv) errorDiv.style.display = "block";
        if (resultDiv) resultDiv.style.display = "none";
    }

    function hidePredictionError() {
        const errorDiv = document.getElementById("prediction-error");
        if (errorDiv) errorDiv.style.display = "none";
    }
});


