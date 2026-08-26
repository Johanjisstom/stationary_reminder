const addBtn = document.getElementById("add-note");
const container = document.getElementById("notes-container");

let notes = JSON.parse(localStorage.getItem("stickyNotes")) || [];

// Save to localstorage
function saveNotes() {
  localStorage.setItem("stickyNotes", JSON.stringify(notes));
}

// create dynamic notes
function createNote({ id, content, x = 100, y = 100 }) {
  const noteEl = document.createElement("div");
  noteEl.className = "note";
  noteEl.style.left = `${x}px`;
  noteEl.style.top = `${y}px`;

  // create textarea
  const textarea = document.createElement("textarea");
  textarea.value = content;

  // create delete button
  const deleteBtn = document.createElement("button");
  deleteBtn.className = "delete";
  deleteBtn.innerText = "×";

  // create move icon
  const moveIcon = document.createElement("div");
  moveIcon.className = "move-icon";
  moveIcon.innerText = "☰";

  // appending node to DOM
  noteEl.appendChild(textarea);
  noteEl.appendChild(deleteBtn);
  noteEl.appendChild(moveIcon);
  container.appendChild(noteEl);

  // Drag
  let offsetX, offsetY;
  noteEl.addEventListener("mousedown", (e) => {
    if (e.target.tagName === "TEXTAREA") return;
    offsetX = e.offsetX;
    offsetY = e.offsetY;

    function onMouseMove(ev) {
      noteEl.style.left = `${ev.pageX - offsetX}px`;
      noteEl.style.top = `${ev.pageY - offsetY}px`;
    }

    function onMouseUp() {
      const idx = notes.findIndex((n) => n.id === id);
      notes[idx].x = parseInt(noteEl.style.left);
      notes[idx].y = parseInt(noteEl.style.top);
      saveNotes();
      document.removeEventListener("mousemove", onMouseMove);
      document.removeEventListener("mouseup", onMouseUp);
    }

    document.addEventListener("mousemove", onMouseMove);
    document.addEventListener("mouseup", onMouseUp);
  });

  // Close note listener
  deleteBtn.addEventListener("click", () => {
    noteEl.remove();
    notes = notes.filter((n) => n.id !== id);
    saveNotes();
  });

  // update note
  textarea.addEventListener("input", () => {
    const idx = notes.findIndex((n) => n.id === id);
    notes[idx].content = textarea.value;
    saveNotes();
  });
}

// Click listener
addBtn.addEventListener("click", () => {
  const newNote = {
    id: Date.now(),
    content: "",
    x: 100,
    y: 100,
  };
  notes.push(newNote);
  saveNotes();
  createNote(newNote);
});

// Load saved notes
notes.forEach((note) => createNote(note));