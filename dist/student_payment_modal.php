<?php
// include 'student_ledger_sidebar.php';
?>

<div class="modal fade" id="studentModal" tabindex="-1" aria-labelledby="studentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="studentModalLabel">Student Payment Information</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Modal Content Starts Here -->
        <h6><strong>Student Information</strong></h6>
        <p style="display:none;"><strong>Student ID:</strong> <span id="modalStudentId"></span></p>
        <p><strong>Student Name:</strong> <span id="modalStudentName"></span></p>
        <p><strong>Grade & Section:</strong> <span id="modalGradeSection"></span></p>

        <div class="alert alert-success mt-3" style="background-color: #3EAA49; color: #fff; border-color: #3EAA49;">
          <strong>Balance as of <span id="modalBalanceDate"></span>:</strong><br>
          <span class="fs-4 fw-bold" id="modalBalanceAmount"></span>
        </div>

        <h6 class="mt-4"><strong>Payment Information</strong></h6>
        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <!-- Add these two hidden headers at the start -->
            <thead class="table-light">
              <tr>
                <th style="display:none;">Student ID</th>
                <th style="display:none;">Payment ID</th>
                <th>Payment Type</th>
                <th>Total Amount</th>
                <th>Remaining Balance</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="modalPaymentTable">
              <!-- Payment rows will be injected here -->
            </tbody>
          </table>
        </div>

<div class="d-flex justify-content-between align-items-center mt-3">
  <button class="btn btn-outline-primary d-flex align-items-center gap-2"
          data-bs-toggle="modal"
          data-bs-target="#recentTransactionsModal"
          id="viewRecentTransactionsBtn">
    <i class="bi bi-clock-history"></i>
    View Recent Transactions
  </button>

  <button type="button" class="btn btn-danger" id="exportPaymentSummaryBtn">
    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Export Payment Summary (PDF)
  </button>
</div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <!-- <button type="button" class="btn btn-success" data-bs-dismiss="modal">Save</button> -->
      </div>
    </div>
  </div>
</div>

<!-- Pay Modal -->
<div class="modal fade" id="payModal" tabindex="-1" aria-labelledby="payModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="payModalLabel">Pay Fee</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>Payment Type:</strong> <span id="payType"></span></p>
        <p><strong>Total Amount:</strong> <span id="payTotal"></span></p>
        <p><strong>Remaining Balance:</strong> <span id="payRemaining"></span></p>
        <div class="mb-3">
          <label for="payAmount" class="form-label">Amount to Pay</label>
          <input type="number" class="form-control" id="payAmount" min="1" step="any">
        </div>
        <div class="mb-3">
          <label for="paidBy" class="form-label">Paid By</label>
          <input type="text" class="form-control" id="paidBy" placeholder="Enter payer's name">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" id="confirmPayBtn">Confirm Payment</button>
      </div>
    </div>
  </div>
</div>

<!-- Recent Transactions Modal -->
<div class="modal fade" id="recentTransactionsModal" tabindex="-1" aria-labelledby="recentTransactionsLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="recentTransactionsLabel">Recent Transactions</h5>
        <!-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> -->
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <thead class="table-light"></thead>
              <tr>
                <th>Date Paid</th>
                <th>Payment Type</th>
                <th>Amount Paid</th>
                <th>Paid By</th>
              </tr>
            </thead>
            <tbody id="recentTransactionsTable">
              <!-- Transactions will be loaded here -->
            </tbody>
          </table>
        </div>
        <div class="d-flex justify-content-end mt-3">
<button class="btn btn-primary" id="seeAllTransactionsBtn">
  See all transactions
</button>




        </div>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-outline-secondary px-4 py-2 rounded-pill fw-semibold" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-2"></i>Close
        </button>
      </div>
    </div>
  </div>
</div>

<!-- SweetAlert2 for Overpayment Warning -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



<script>
  let currentStudentId = null;
  let showingAll = false;

  // Load transactions: recent or all
  function loadTransactions(studentId, showAll = false) {
    const url = `php_functions/get_recent_transactions.php?student_id=${studentId}${showAll ? '&all=true' : ''}`;

    fetch(url)
      .then(response => response.json())
      .then(data => {
        const tableBody = document.getElementById("recentTransactionsTable");
        tableBody.innerHTML = '';

        if (data.length === 0) {
          tableBody.innerHTML = '<tr><td colspan="4" class="text-center">No transactions found.</td></tr>';
          return;
        }

        data.forEach(transaction => {
          const row = `
            <tr>
              <td>${transaction.date_paid}</td>
              <td>${transaction.payment_type}</td>
              <td>${transaction.amount_paid}</td>
              <td>${transaction.paid_by}</td>
            </tr>
          `;
          tableBody.innerHTML += row;
        });
      })
      .catch(error => {
        console.error('Error loading transactions:', error);
      });
  }

  // Event: View Recent Transactions button in student modal
  document.getElementById("viewRecentTransactionsBtn").addEventListener("click", function () {
    currentStudentId = document.getElementById("modalStudentId").textContent.trim();
    if (currentStudentId) {
      showingAll = false;
      loadTransactions(currentStudentId, false); // Load recent 10
      document.getElementById("seeAllTransactionsBtn").textContent = "See all transactions";
    }
  });

  // Toggle all/recent transactions
  document.getElementById("seeAllTransactionsBtn").addEventListener("click", function () {
    if (!currentStudentId) return;

    showingAll = !showingAll;
    loadTransactions(currentStudentId, showingAll);

    this.textContent = showingAll ? "Show less" : "See all transactions";
  });
</script>


<script>
  document.getElementById("exportPaymentSummaryBtn").addEventListener("click", function () {
    const studentId = document.getElementById("modalStudentId").textContent.trim();
    if (studentId) {
      const link = document.createElement('a');
      link.href = `php_functions/export_student_ledger_pdf.php?student_id=${studentId}`;
      link.download = `student_ledger_${studentId}.pdf`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    } else {
      alert("Student ID is missing.");
    }
  });
</script>
