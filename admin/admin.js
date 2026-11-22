/* PULSE admin: shows a preview of a chosen image or video before it is saved,
   and warns straight away about files that cannot be used. */
(function () {
  const LIMITS = { image: 10, video: 60 }; // MB, same limits as the server
  const IMAGE_TYPES = /^image\/(jpeg|png|webp|gif)$/;

  document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
    const shape = input.dataset.preview;
    const isVideo = shape === "video";
    const kind = isVideo ? "video" : "image";

    const box = document.createElement("div");
    box.className = "upload-preview";
    box.hidden = true;
    const label = document.createElement("span");
    label.className = "upload-label";
    label.textContent = "New " + kind + " · not saved yet";
    const frame = document.createElement("div");
    frame.className = "upload-frame upload-" + shape;
    const note = document.createElement("p");
    note.className = "help";
    const remove = document.createElement("button");
    remove.type = "button";
    remove.className = "btn btn-small btn-ghost";
    remove.textContent = "Remove";
    box.append(label, frame, note, remove);
    input.insertAdjacentElement("afterend", box);

    let url = null;
    const reset = () => {
      if (url) URL.revokeObjectURL(url);
      url = null;
      frame.replaceChildren();
      note.classList.remove("help-error");
      input.setCustomValidity("");
    };
    const fail = message => {
      note.textContent = message;
      note.classList.add("help-error");
      input.setCustomValidity(message);
    };

    input.addEventListener("change", () => {
      reset();
      const file = input.files && input.files[0];
      if (!file) { box.hidden = true; return; }
      box.hidden = false;
      const sizeMb = file.size / (1024 * 1024);
      const sizeText = sizeMb >= 1 ? sizeMb.toFixed(1) + " MB" : Math.max(1, Math.round(file.size / 1024)) + " KB";

      const typeOk = isVideo ? file.type === "video/mp4" : IMAGE_TYPES.test(file.type);
      if (!typeOk) {
        frame.hidden = true;
        fail(file.name + " cannot be used. Please choose " + (isVideo ? "an MP4 video." : "a JPG, PNG, WebP or GIF image."));
        return;
      }
      frame.hidden = false;
      url = URL.createObjectURL(file);
      const media = document.createElement(isVideo ? "video" : "img");
      media.src = url;
      if (isVideo) { media.muted = true; media.autoplay = true; media.loop = true; media.playsInline = true; }
      else media.alt = "Preview of " + file.name;
      media.addEventListener("error", () => { frame.hidden = true; fail(file.name + " could not be opened. The file may be damaged; please choose another one."); });
      frame.append(media);

      if (sizeMb > LIMITS[kind]) {
        fail(file.name + " is " + sizeText + ". The limit is " + LIMITS[kind] + " MB, so please choose a smaller file.");
        return;
      }
      note.textContent = file.name + " · " + sizeText + ". If this is the right " + kind + ", press Save. Otherwise press Remove or choose another file.";
    });

    remove.addEventListener("click", () => {
      input.value = "";
      input.dispatchEvent(new Event("change"));
      input.focus();
    });
  });
})();
