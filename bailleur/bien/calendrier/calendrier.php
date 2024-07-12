<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Calendrier de Blocage pour Bailleurs</title>
    <link href='https://unpkg.com/fullcalendar@5.5.1/main.css' rel='stylesheet' />
    <script src='https://unpkg.com/fullcalendar@5.5.1/main.js'></script>
    <script src='https://unpkg.com/fullcalendar@5.5.1/locales-all.js'></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        #calendar {
            max-width: 900px;
            margin: 40px auto;
        }
    </style>
</head>
<body>
  <main>
<div id='calendar'></div>
<!-- Modal pour la réservation -->
<div class="modal fade" id="reservationModal" tabindex="-1" role="dialog" aria-labelledby="reservationModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="reservationModalLabel">Détails de la date</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="reservationForm">
          <div class="form-group">
            <label for="date_debut">Date de début</label>
            <input type="date" class="form-control" id="date_debut" name="date_debut" readonly>
          </div>
          <div class="form-group">
            <label for="date_fin">Date de fin</label>
            <input type="date" class="form-control" id="date_fin" name="date_fin" readonly>
          </div>
          <div class="form-group">
            <label for="status">Statut</label>
            <select class="form-control" id="status" name="status">
              <option value="blocked">Bloquer</option>
              <option value="available">Debloquer</option>
            </select>
          </div>
          <input type="hidden" id="id_bien" name="id_bien" value="<?php echo $_GET['id']; ?>">
          <input type="hidden" id="id_utilisateur" name="id_utilisateur" value="<?php echo $_SESSION['id_utilisateur']; ?>">
          <button type="submit" class="btn btn-primary">Enregistrer</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var idBien = <?php echo $_GET['id']; ?>;

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'fr',
            headerToolbar: {
                left: 'prev',
                center: 'title',
                right: 'next'
            },
            events: `get_occupations.php?id=${idBien}`,
            selectable: true,
            select: function(info) {
                $('#date_debut').val(info.startStr);
                $('#date_fin').val(info.endStr);
                $('#reservationModal').modal('show');
            },
            validRange: {
                start: new Date().toISOString().split("T")[0] // disable past dates
            },
            eventClick: function(info) {
                alert('Raison: ' + info.event.extendedProps.raison_indispo);
            },
            editable: true,
            droppable: true,
        });

        calendar.render();

        $('#reservationForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            $.ajax({
                type: 'POST',
                url: 'update_date_status.php',
                data: formData,
                success: function(response) {
                    alert(response);
                    $('#reservationModal').modal('hide');
                    calendar.refetchEvents(); // Refresh the calendar events
                }
            });
        });
    });
</script>
  </main>
</body>
</html>
