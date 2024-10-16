tinymce.init({
  selector: "textarea#default",
});

window.addEventListener("DOMContentLoaded", () => {
  const quill = new Quill("#editor", {
    theme: "snow",
  });

  let toolbar = document.querySelector(".ql-toolbar.ql-snow");
  toolbar.style.background = "white";
  
});
