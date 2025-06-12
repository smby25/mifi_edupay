<!-- Settings Modal -->
<div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content rounded-4 shadow">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold" id="settingsModalLabel">
          <i class="bi bi-gear me-2 text-dark"></i>Settings
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Nav tabs -->
        <ul class="nav nav-tabs mb-3" id="settingsTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="backup-tab" data-bs-toggle="tab" data-bs-target="#backup" type="button" role="tab" aria-controls="backup" aria-selected="true">
              <i class="bi bi-download"></i> Backup Database
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="import-tab" data-bs-toggle="tab" data-bs-target="#import" type="button" role="tab" aria-controls="import" aria-selected="false">
              <i class="bi bi-upload"></i> Import Database
            </button>
          </li>
        </ul>
        <div class="tab-content" id="settingsTabContent">
          <!-- Backup Tab -->
          <div class="tab-pane fade show active" id="backup" role="tabpanel" aria-labelledby="backup-tab">
            <div class="mb-3">
              <p>Click the button below to download a backup of your database.</p>
              <form method="POST" action="php_functions/backup_db.php">
                <button type="submit" class="btn btn-primary">
                  <i class="bi bi-download"></i> Backup Now
                </button>
              </form>
            </div>
          </div>
          <!-- Import Tab -->
          <div class="tab-pane fade" id="import" role="tabpanel" aria-labelledby="import-tab">
            <div class="mb-3">
              <p>Import a database backup (.sql file):</p>
              <form method="POST" action="php_functions/import_db.php" enctype="multipart/form-data">
                <input type="file" name="import_file" accept=".sql" class="form-control mb-2" required>
                <button type="submit" class="btn btn-success">
                  <i class="bi bi-upload"></i> Import
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Open settings modal when clicking Settings in profile dropdown
$(document).ready(function() {
    $('a[href="settings.php"]').on('click', function(e) {
        e.preventDefault();
        $('#settingsModal').modal('show');
    });
});
</script>