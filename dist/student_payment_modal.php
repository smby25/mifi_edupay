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

        <button class="btn btn-outline-primary d-flex align-items-center gap-2">
          <i class="bi bi-clock-history"></i>
          View Recent Transactions
        </button>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success">Save</button>
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
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-outline-secondary px-4 py-2 rounded-pill fw-semibold" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-2"></i>Close
        </button>
      </div>
    </div>
  </div>
</div>
