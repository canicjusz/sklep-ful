class ColorSelector extends HTMLElement {
  constructor() {
    super();
    this.selectTemplate = document.querySelector("#color-selector");
    //trzeba stworzyc shadow, zeby uzywac slotow
    this.attachShadow({ mode: "open" });
    const clone = this.selectTemplate.content.cloneNode(true);
    this.shadowRoot.appendChild(clone);
    this.listElement = this.shadowRoot.querySelector(".select__list");
    const slot = this.shadowRoot.querySelector("slot");
    this.list = [...slot.assignedElements()];
    const selected = this.getAttribute("data-selected")
    this.selected = selected.length > 0 ? selected.split(",") : [];

    this.renderColors();
    this.setEventListeners();
  }

  renderColors() {
    this.list.forEach((el) => {
      const valueId = el.getAttribute("data-value-id");
      const value = el.getAttribute("data-value");
      el.style.background = value;
      if (this.selected.includes(valueId)) {
        el.classList.add("color-selector__list-item--selected");
      }
    });
  }

  setEventListeners() {
    this.list.forEach((el) => {
      el.addEventListener("click", (_) => {
        const valueId = el.getAttribute("data-value-id");
        el.classList.toggle("color-selector__list-item--selected");
        const valueIndex = this.selected.indexOf(valueId);
        if (valueIndex !== -1) {
          this.selected.splice(valueIndex, 1);
        } else {
          this.selected.push(valueId);
        }
        this.dispatchEvent(
          new CustomEvent("selectionchange", {
            detail: {
              value: valueId,
              is_selected: !valueIndex,
              selected: this.selected
            },
          })
        );
      });
    });
  }
}
customElements.define("color-selector", ColorSelector);

document
  .querySelector(".color-selector")
  .addEventListener("selectionchange", (e) => {
    const url = new URL(location.href)
    const joinedColorIDs = e.detail.selected.join(',')
    url.searchParams.set('colors', joinedColorIDs)
    location.href = url.pathname + '?' + url.searchParams.toString()
    // const 
  });
