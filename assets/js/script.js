document.addEventListener("DOMContentLoaded", function () {
  // =========================================================================
  // 1. HANDLER FORM EVENT (Tambah & Edit via form_event.php)
  // =========================================================================
  const eventFormPage = document.getElementById("eventFormPage");

  if (eventFormPage) {
    eventFormPage.addEventListener("submit", async (e) => {
      e.preventDefault();

      // Loading state
      Swal.fire({
        title: "Menyimpan Data...",
        text: "Mohon tunggu, sedang memproses data dan file.",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
      });

      try {
        const formData = new FormData(eventFormPage);

        const response = await fetch("crud_event.php", {
          method: "POST",
          body: formData,
        });

        // Robust JSON Parsing
        const text = await response.text();
        let result;
        try {
          // Coba cari kurung kurawal pembuka JSON pertama (antisipasi warning PHP)
          const jsonStartIndex = text.indexOf("{");
          if (jsonStartIndex > -1) {
            const jsonString = text.substring(jsonStartIndex);
            result = JSON.parse(jsonString);
          } else {
            throw new Error("Format respon tidak valid");
          }
        } catch (err) {
          console.error("Raw Response:", text);
          throw new Error("Server Error: Respon tidak valid.");
        }

        if (result.status === "success") {
          await Swal.fire({
            icon: "success",
            title: "Berhasil!",
            text: result.message,
            timer: 1500,
            showConfirmButton: false,
          });
          window.location.href = "kelola_event.php"; // Redirect
        } else {
          Swal.fire({ icon: "error", title: "Gagal!", text: result.message });
        }
      } catch (error) {
        console.error("Error:", error);
        Swal.fire({
          icon: "error",
          title: "Oops!",
          text: "Terjadi kesalahan koneksi atau server.",
        });
      }
    });
  }

  // =========================================================================
  // 2. HANDLER HAPUS EVENT
  // =========================================================================
  document.querySelectorAll(".hapus-btn").forEach((button) => {
    button.addEventListener("click", function () {
      const eventId = this.dataset.id;
      const eventJudul = this.dataset.judul;

      Swal.fire({
        title: "Hapus Event?",
        html: `Anda yakin ingin menghapus event:<br><b>${eventJudul}</b>?<br><span class="text-sm text-red-500">Semua data pendaftar juga akan terhapus!</span>`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        confirmButtonText: "Ya, Hapus!",
        cancelButtonText: "Batal",
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            const formData = new FormData();
            formData.append("action", "hapus");
            formData.append("event_id", eventId);

            const response = await fetch("crud_event.php", {
              method: "POST",
              body: formData,
            });
            const data = await response.json();

            if (data.status === "success") {
              Swal.fire("Terhapus!", data.message, "success").then(() =>
                window.location.reload()
              );
            } else {
              Swal.fire("Gagal", data.message, "error");
            }
          } catch (e) {
            Swal.fire("Error", "Gagal menghapus data.", "error");
          }
        }
      });
    });
  });

  // =========================================================================
  // 3. VISUAL CERTIFICATE EDITOR (LENGKAP)
  // =========================================================================
  const openVisualEditorBtn = document.getElementById("open-visual-editor-btn");

  if (openVisualEditorBtn) {
    const visualEditorModal = document.getElementById("visualEditorModal");
    const previewContainer = document.getElementById("certificate-preview");
    const dragNama = document.getElementById("drag-nama");
    const dragNomor = document.getElementById("drag-nomor");
    const savePositionsBtn = document.getElementById("save-positions-btn");
    const modalCloseBtn = visualEditorModal?.querySelector(
      "button[onclick*='closeModal']"
    );

    const fsNamaInput = document.getElementById("fs-nama");
    const fsNomorInput = document.getElementById("fs-nomor");
    const fontSelect = document.getElementById("sertifikat_font_edit");

    const A4_PORTRAIT = { width: 2480, height: 3508 };
    const A4_LANDSCAPE = { width: 3508, height: 2480 };

    function updateStyles() {
      const orientasiSelect = document.getElementById(
        "sertifikat_orientasi_edit"
      );
      const orientasi = orientasiSelect ? orientasiSelect.value : "portrait";
      const A4_DIMENSIONS =
        orientasi === "landscape" ? A4_LANDSCAPE : A4_PORTRAIT;

      if (previewContainer) {
        // Update Ukuran
        const previewWidth = previewContainer.clientWidth;
        const fontRatio = previewWidth / A4_DIMENSIONS.width;

        const namaFs = parseFloat(fsNamaInput.value) || 120;
        const nomorFs = parseFloat(fsNomorInput.value) || 40;

        if (dragNama) dragNama.style.fontSize = `${namaFs * fontRatio}px`;
        if (dragNomor) dragNomor.style.fontSize = `${nomorFs * fontRatio}px`;

        // Update Font Family
        if (fontSelect) {
          const fontFile = fontSelect.value;
          const fontFamily = fontFile.replace(/\.(ttf|otf)$/i, "");
          if (dragNama) dragNama.style.fontFamily = fontFamily;
          if (dragNomor) dragNomor.style.fontFamily = fontFamily;
        }
      }
    }

    openVisualEditorBtn.addEventListener("click", function () {
      const templateInput = document.getElementById(
        "sertifikat_template_lama_edit"
      );
      const templateFile = templateInput ? templateInput.value : "";

      if (!templateFile) {
        Swal.fire(
          "Info",
          "Upload dan simpan template sertifikat terlebih dahulu.",
          "warning"
        );
        return;
      }

      previewContainer.style.backgroundImage = `url('../assets/img/sertifikat_templates/${templateFile}')`;

      const orientasiSelect = document.getElementById(
        "sertifikat_orientasi_edit"
      );
      const orientasi = orientasiSelect ? orientasiSelect.value : "portrait";
      const A4_DIMENSIONS =
        orientasi === "landscape" ? A4_LANDSCAPE : A4_PORTRAIT;

      const previewWidth = previewContainer.clientWidth;
      const previewHeight =
        (previewWidth * A4_DIMENSIONS.height) / A4_DIMENSIONS.width;
      previewContainer.style.height = `${previewHeight}px`;

      // Posisi
      let namaX =
        parseFloat(
          document.getElementById("sertifikat_nama_x_percent_edit").value
        ) || 50;
      let namaY =
        parseFloat(
          document.getElementById("sertifikat_nama_y_percent_edit").value
        ) || 50;
      let nomorX =
        parseFloat(
          document.getElementById("sertifikat_nomor_x_percent_edit").value
        ) || 50;
      let nomorY =
        parseFloat(
          document.getElementById("sertifikat_nomor_y_percent_edit").value
        ) || 60;

      if (dragNama) {
        dragNama.style.left = `${namaX}%`;
        dragNama.style.top = `${namaY}%`;
        dragNama.setAttribute("data-x", namaX);
        dragNama.setAttribute("data-y", namaY);
        dragNama.style.transform = "translate(0, 0)";
      }
      if (dragNomor) {
        dragNomor.style.left = `${nomorX}%`;
        dragNomor.style.top = `${nomorY}%`;
        dragNomor.setAttribute("data-x", nomorX);
        dragNomor.setAttribute("data-y", nomorY);
        dragNomor.style.transform = "translate(0, 0)";
      }

      if (fsNamaInput)
        fsNamaInput.value =
          document.getElementById("sertifikat_nama_fs_edit").value || 120;
      if (fsNomorInput)
        fsNomorInput.value =
          document.getElementById("sertifikat_nomor_fs_edit").value || 40;

      updateStyles();

      if (typeof window.openModal === "function") {
        window.openModal(visualEditorModal);
      } else {
        visualEditorModal.classList.remove("hidden");
        visualEditorModal.classList.add("flex");
      }
    });

    if (fsNamaInput) fsNamaInput.addEventListener("input", updateStyles);
    if (fsNomorInput) fsNomorInput.addEventListener("input", updateStyles);
    if (fontSelect) fontSelect.addEventListener("change", updateStyles);

    if (savePositionsBtn) {
      savePositionsBtn.addEventListener("click", function () {
        const namaX = dragNama.getAttribute("data-x");
        const namaY = dragNama.getAttribute("data-y");
        const nomorX = dragNomor.getAttribute("data-x");
        const nomorY = dragNomor.getAttribute("data-y");

        document.getElementById("sertifikat_nama_x_percent_edit").value =
          parseFloat(namaX).toFixed(2);
        document.getElementById("sertifikat_nama_y_percent_edit").value =
          parseFloat(namaY).toFixed(2);
        document.getElementById("sertifikat_nomor_x_percent_edit").value =
          parseFloat(nomorX).toFixed(2);
        document.getElementById("sertifikat_nomor_y_percent_edit").value =
          parseFloat(nomorY).toFixed(2);

        document.getElementById("sertifikat_nama_fs_edit").value =
          fsNamaInput.value;
        document.getElementById("sertifikat_nomor_fs_edit").value =
          fsNomorInput.value;

        if (typeof window.closeModal === "function") {
          window.closeModal(visualEditorModal);
        } else {
          visualEditorModal.classList.add("hidden");
          visualEditorModal.classList.remove("flex");
        }

        const Toast = Swal.mixin({
          toast: true,
          position: "top-end",
          showConfirmButton: false,
          timer: 3000,
        });
        Toast.fire({
          icon: "success",
          title: "Posisi disimpan sementara. Klik Simpan Perubahan di bawah.",
        });
      });
    }

    // Tutup Modal Manual
    if (modalCloseBtn) {
      modalCloseBtn.addEventListener("click", function () {
        if (typeof window.closeModal === "function") {
          window.closeModal(visualEditorModal);
        } else {
          visualEditorModal.classList.add("hidden");
          visualEditorModal.classList.remove("flex");
        }
      });
    }

    if (typeof interact !== "undefined") {
      interact(".draggable").draggable({
        listeners: {
          move(event) {
            const target = event.target;
            const container = document.getElementById("certificate-preview");
            const containerRect = container.getBoundingClientRect();

            let x = parseFloat(target.getAttribute("data-x")) || 0;
            let y = parseFloat(target.getAttribute("data-y")) || 0;

            x += (event.dx / containerRect.width) * 100;
            y += (event.dy / containerRect.height) * 100;

            target.style.left = `${x}%`;
            target.style.top = `${y}%`;
            target.setAttribute("data-x", x);
            target.setAttribute("data-y", y);
          },
        },
        modifiers: [
          interact.modifiers.restrict({
            restriction: "parent",
            elementRect: { top: 0, left: 0, bottom: 1, right: 1 },
            endOnly: false,
          }),
        ],
      });
    }
  }

  // =========================================================================
  // 4. KELOLA PERTANYAAN TAMBAHAN (FORM FIELDS)
  // =========================================================================
  const fieldModal = document.getElementById("fieldModal");
  if (fieldModal) {
    const btnTambahField = document.getElementById("btnTambahField");
    const fieldForm = document.getElementById("fieldForm");
    const optionsContainer = document.getElementById("formOptionsContainer");
    const formFieldType = document.getElementById("formFieldType");

    if (formFieldType) {
      formFieldType.addEventListener("change", function () {
        if (this.value === "select" || this.value === "radio") {
          optionsContainer.classList.remove("hidden");
        } else {
          optionsContainer.classList.add("hidden");
        }
      });
    }

    if (btnTambahField) {
      btnTambahField.addEventListener("click", function () {
        fieldForm.reset();
        document.getElementById("modalTitle").textContent =
          "Tambah Pertanyaan Baru";
        document.getElementById("formAction").value = "tambah";
        document.getElementById("formFieldId").value = "";
        document.getElementById("formWorkshopId").value =
          this.dataset.workshopId;
        optionsContainer.classList.add("hidden");
        window.openModal(fieldModal);
      });
    }

    document.body.addEventListener("click", function (e) {
      const btnEdit = e.target.closest(".btn-edit-field");
      if (btnEdit) {
        const d = btnEdit.dataset;
        fieldForm.reset();
        document.getElementById("modalTitle").textContent = "Edit Pertanyaan";
        document.getElementById("formAction").value = "edit";
        document.getElementById("formFieldId").value = d.fieldId;
        document.getElementById("formWorkshopId").value = d.workshopId;
        document.getElementById("formLabel").value = d.label;
        document.getElementById("formFieldType").value = d.fieldType;
        document.getElementById("formOptions").value = d.options;
        document.getElementById("formIsRequired").checked = d.isRequired == 1;
        formFieldType.dispatchEvent(new Event("change"));
        window.openModal(fieldModal);
      }
    });

    document.body.addEventListener("click", function (e) {
      const btnHapus = e.target.closest(".btn-hapus-field");
      if (btnHapus) {
        const label = btnHapus.dataset.label;
        Swal.fire({
          title: "Hapus Pertanyaan?",
          text: `Anda yakin menghapus: "${label}"?`,
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#d33",
          confirmButtonText: "Ya, Hapus",
        }).then(async (result) => {
          if (result.isConfirmed) {
            const fd = new FormData();
            fd.append("action", "hapus");
            fd.append("field_id", btnHapus.dataset.fieldId);
            try {
              const res = await fetch("crud_form_fields.php", {
                method: "POST",
                body: fd,
              });
              const json = await res.json();
              if (json.status === "success") {
                Swal.fire("Berhasil", json.message, "success").then(() =>
                  location.reload()
                );
              } else {
                Swal.fire("Gagal", json.message, "error");
              }
            } catch (err) {
              Swal.fire("Error", "Gagal koneksi server", "error");
            }
          }
        });
      }
    });

    if (fieldForm) {
      fieldForm.addEventListener("submit", async function (e) {
        e.preventDefault();
        const fd = new FormData(this);
        try {
          const res = await fetch("crud_form_fields.php", {
            method: "POST",
            body: fd,
          });
          const json = await res.json();
          if (json.status === "success") {
            window.closeModal(fieldModal);
            Swal.fire("Berhasil", json.message, "success").then(() =>
              location.reload()
            );
          } else {
            Swal.fire("Gagal", json.message, "error");
          }
        } catch (err) {
          Swal.fire("Error", "Gagal koneksi server", "error");
        }
      });
    }
  }

  // =========================================================================
  // 5. PESERTA (CHECK-IN / CHECK-OUT / DENDA)
  // =========================================================================
  document.querySelectorAll(".checkin-btn").forEach((button) => {
    button.addEventListener("click", async function () {
      const id = this.dataset.id;

      Swal.fire({
        title: "Memproses...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
      });

      try {
        const fd = new FormData();
        fd.append("pendaftaran_id", id);

        const response = await fetch("proses_checkin.php", {
          method: "POST",
          body: fd,
        });
        const result = await response.json();

        if (result.status === "success") {
          // Berhasil Check-in atau Check-out normal
          let iconType = result.type === "checkin" ? "success" : "info";
          let titleText =
            result.type === "checkin"
              ? "Check-in Berhasil"
              : "Check-out Berhasil";

          Swal.fire({
            icon: iconType,
            title: titleText,
            html: `Peserta: <b>${result.nama}</b><br>Waktu: ${result.waktu}`,
            timer: 2000,
            showConfirmButton: false,
          }).then(() => location.reload());
        } else if (result.status === "denda") {
          // Logic Denda
          Swal.fire({
            title: "⚠️ TERLAMBAT CHECK-OUT!",
            html: `
                            <div class="text-left bg-red-50 p-4 rounded-lg border border-red-200 mb-4">
                                <p>Peserta: <b>${result.nama}</b></p>
                                <p>Batas Waktu: ${result.batas}</p>
                                <p class="mt-2 text-red-600 font-bold text-lg">Total Denda: Rp ${result.denda}</p>
                            </div>
                            <p class="text-sm text-gray-500 mb-2">Scan QRIS untuk membayar:</p>
                            <img src="../assets/img/qris_default.jpg" class="w-48 h-48 mx-auto border border-gray-300 rounded p-1 mb-2">
                            <p class="text-xs text-gray-400">Tunjukkan bukti bayar ke petugas.</p>
                        `,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sudah Bayar & Check-out",
            confirmButtonColor: "#d33",
            cancelButtonText: "Batal",
          }).then((res) => {
            if (res.isConfirmed) {
              // Konfirmasi bayar -> reload agar data terupdate (atau panggil endpoint bayar)
              Swal.fire(
                "Selesai",
                "Check-out berhasil dicatat.",
                "success"
              ).then(() => location.reload());
            }
          });
        } else {
          Swal.fire("Gagal", result.message, "error");
        }
      } catch (err) {
        console.error(err);
        Swal.fire("Error", "Gagal koneksi.", "error");
      }
    });
  });

  // Kirim Sertifikat
  document.querySelectorAll(".kirim-sertifikat-btn").forEach((button) => {
    button.addEventListener("click", function () {
      const id = this.dataset.id;
      const nama = this.dataset.nama;
      const btn = this;

      Swal.fire({
        title: "Kirim Sertifikat?",
        html: `Kirim email sertifikat ke <b>${nama}</b>?`,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Ya, Kirim",
      }).then(async (result) => {
        if (result.isConfirmed) {
          Swal.fire({
            title: "Mengirim...",
            didOpen: () => Swal.showLoading(),
          });
          btn.disabled = true;

          try {
            const fd = new FormData();
            fd.append("id", id);

            const res = await fetch("proses_kirim_sertifikat.php", {
              method: "POST",
              body: fd,
            });
            const text = await res.text();

            let json;
            try {
              json = JSON.parse(text);
            } catch (e) {
              const start = text.indexOf("{");
              json = JSON.parse(text.substring(start));
            }

            if (json.status === "success") {
              Swal.fire("Terkirim!", json.message, "success").then(() =>
                location.reload()
              );
            } else {
              Swal.fire("Gagal", json.message, "error");
              btn.disabled = false;
            }
          } catch (err) {
            Swal.fire("Error", "Gagal mengirim request.", "error");
            btn.disabled = false;
          }
        }
      });
    });
  });

  // Handler Upload Font (Halaman Kelola Font)
  const uploadFontForm = document.getElementById("uploadFontForm");
  if (uploadFontForm) {
    uploadFontForm.addEventListener("submit", async function (e) {
      e.preventDefault();
      const formData = new FormData(this);
      Swal.fire({
        title: "Mengupload Font...",
        didOpen: () => Swal.showLoading(),
      });

      try {
        const response = await fetch("crud_font.php", {
          method: "POST",
          body: formData,
        });
        const result = await response.json();
        if (result.status === "success") {
          Swal.fire("Berhasil!", result.message, "success").then(() =>
            location.reload()
          );
        } else {
          Swal.fire("Gagal", result.message, "error");
        }
      } catch (error) {
        Swal.fire("Error", "Gagal koneksi server.", "error");
      }
    });
  }

  // Handler Hapus Font
  document.querySelectorAll(".hapus-font-btn").forEach((button) => {
    button.addEventListener("click", function () {
      const filename = this.dataset.filename;
      Swal.fire({
        title: "Hapus Font?",
        text: `Yakin hapus: ${filename}?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        confirmButtonText: "Ya, Hapus",
      }).then(async (result) => {
        if (result.isConfirmed) {
          const fd = new FormData();
          fd.append("action", "hapus");
          fd.append("filename", filename);
          try {
            const response = await fetch("crud_font.php", {
              method: "POST",
              body: fd,
            });
            const res = await response.json();
            if (res.status === "success") {
              Swal.fire("Terhapus!", res.message, "success").then(() =>
                location.reload()
              );
            } else {
              Swal.fire("Gagal", res.message, "error");
            }
          } catch (e) {
            Swal.fire("Error", "Gagal koneksi.", "error");
          }
        }
      });
    });
  });
});
