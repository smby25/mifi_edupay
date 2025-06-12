<div class="modal fade" id="addFeesModal" tabindex="-1" aria-labelledby="addFeesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg"> <!-- large modal -->
    <div class="modal-content">
      <form action="php_functions/save_payments.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="addFeesModalLabel">Add Payment Batch</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
<input type="hidden" id="editPaymentId" name="payment_id" value="">
        <div class="modal-body">
          <div class="mb-3">
            <label for="batchName" class="form-label">Batch Name</label>
            <input type="text" name="batch_name" id="batchName" class="form-control" required>
          </div>

          <div class="mb-3">
            <label for="targetGrade" class="form-label">Target Grade</label>
            <select name="target_grade" id="targetGrade" class="form-select" required>
                <option value="" selected disabled>Choose grade</option>
                <option value="7">7</option>
                <option value="8">8</option>
                <option value="9">9</option>
                <option value="10">10</option>
                <option value="11">11</option>
                <option value="12">12</option>
            </select>
          </div>

          <div id="payment-list">
            <div class="row mb-2 payment-item">
              <div class="col-md-6">
                <input type="text" name="payment_type[]" class="form-control" placeholder="Payment Type (e.g. Tuition)" required>
              </div>
              <div class="col-md-4">
                <input type="number" name="amount[]" class="form-control" placeholder="Amount" step="0.01" required>
              </div>
              <div class="col-md-2">
                <button type="button" class="btn btn-danger w-100 remove-payment-btn" onclick="removePaymentRow(this)">Remove</button>
              </div>
            </div>
          </div>

          <div class="text-end">
            <button type="button" class="btn btn-secondary" id="addPaymentTypeBtn" onclick="addPaymentRow()">+ Add Payment Type</button>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="savePaymentsBtn">Save Payments</button>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // SweetAlert for Save Payments (already present)
  const saveBtn = document.getElementById('savePaymentsBtn');
  if (saveBtn) {
    saveBtn.addEventListener('click', function(e) {
      Swal.fire({
        title: 'Are you sure?',
        text: "Do you want to save these payment details?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, save it!'
      }).then((result) => {
        if (result.isConfirmed) {
          saveBtn.closest('form').submit();
        }
      });
    });
  }

  // Reset form and payment list when modal is closed
  $('#addFeesModal').on('hidden.bs.modal', function () {
    const form = this.querySelector('form');
    if (form) form.reset();

    // Reset payment-list to default single row
    $('#payment-list').html(`
      <div class="row mb-2 payment-item">
        <div class="col-md-6">
          <input type="text" name="payment_type[]" class="form-control" placeholder="Payment Type (e.g. Tuition)" required>
        </div>
        <div class="col-md-4">
          <input type="number" name="amount[]" class="form-control" placeholder="Amount" step="0.01" required>
        </div>
        <div class="col-md-2">
          <button type="button" class="btn btn-danger w-100 remove-payment-btn" onclick="removePaymentRow(this)">Remove</button>
        </div>
      </div>
    `);

    // Reset hidden payment_id
    $('#editPaymentId').val('');
    // Show add/remove buttons
    $('#addPaymentTypeBtn').show();
    $('.remove-payment-btn').show();
  });
});
</script>


<script>
function addPaymentRow() {
  const container = document.getElementById('payment-list');
  const row = document.createElement('div');
  row.classList.add('row', 'mb-2', 'payment-item');
  row.innerHTML = `
    <div class="col-md-6">
      <input type="text" name="payment_type[]" class="form-control" placeholder="Payment Type (e.g. Misc.)" required>
    </div>
    <div class="col-md-4">
      <input type="number" name="amount[]" class="form-control" placeholder="Amount" step="0.01" required>
    </div>
    <div class="col-md-2">
      <button type="button" class="btn btn-danger w-100" onclick="removePaymentRow(this)">Remove</button>
    </div>
  `;
  container.appendChild(row);
}

function removePaymentRow(button) {
  const row = button.closest('.payment-item');
  row.remove();
}
</script>




