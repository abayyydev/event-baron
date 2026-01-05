// Membuat fungsi openModal dan closeModal menjadi GLOBAL
// dengan menempelkannya ke objek 'window'
window.openModal = function (modal) {
  if (modal == null) return;
  modal.classList.add("active");
  document.getElementById("overlay").classList.add("active");
};

window.closeModal = function (modal) {
  if (modal == null) return;
  modal.classList.remove("active");
  document.getElementById("overlay").classList.remove("active");
};

document.addEventListener("DOMContentLoaded", function () {
  // Event listener untuk tombol yang membuka modal secara otomatis via atribut
  const openModalButtons = document.querySelectorAll("[data-modal-target]");
  openModalButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const modal = document.querySelector(button.dataset.modalTarget);
      window.openModal(modal);
    });
  });

  // Event listener untuk tombol yang menutup modal
  const closeModalButtons = document.querySelectorAll("[data-close-button]");
  closeModalButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const modal = button.closest(".modal");
      window.closeModal(modal);
    });
  });

  // Event listener untuk klik di luar modal (di overlay)
});
