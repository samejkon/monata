<template>
  <div class="booking-modal-display d-none" id="booking-modal" onclick="toggleModal()">
    <form class="modal-content" method="" onclick="event.stopPropagation()">
      <div class="modal-item1">
        <h3 class="modal-heading">Check Availability</h3>
      </div>
      <div class="modal-item2">
        <input class="modal-input" type="text" name="checkInDate" id="checkInDate" placeholder="Check in date" />
      </div>
      <div class="modal-item3">
        <input class="modal-input" type="text" name="checkOutDate" id="checkOutDate" placeholder="Check out date" />
      </div>
      <div class="modal-item4">
        <input class="modal-input" type="text" name="adult" id="adult" placeholder="Adult" />
      </div>
      <div class="modal-item5">
        <input class="modal-input" type="text" name="children" id="children" placeholder="Children" />
      </div>
      <div class="modal-item6">
        <input class="modal-input" type="text" name="roomType" id="roomType" placeholder="Room type" />
      </div>
      <div class="modal-item7">
        <button type="submit">Check Availability</button>
      </div>
    </form>
  </div>
</template>