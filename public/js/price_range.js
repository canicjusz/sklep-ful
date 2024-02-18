class PriceRange extends HTMLElement {
  constructor() {
    super();
    this.rangeTemplate = document.querySelector("#range");
    const clone = this.rangeTemplate.content.cloneNode(true);
    this.appendChild(clone);
    this.rangeWidth = this.querySelector(".range__input--bottom").offsetWidth;
    this.rangeBottom = this.querySelector(".range__input--bottom");
    this.rangeTop = this.querySelector(".range__input--top");
    this.inputBottom = this.querySelector(".range__value--bottom");
    this.inputTop = this.querySelector(".range__value--top");
    this.bottomValue = +(this.getAttribute("data-bottom-value") ?? 1);
    this.topValue = +(this.getAttribute("data-top-value") ?? 1);
    this.bottomBoundary = +(this.getAttribute("data-bottom-border") ?? 1);
    this.topBoundary = +(this.getAttribute("data-top-border") ?? 10000);

    this.setDefault();
    this.setEventListeners();
  }

  setDefault() {
    document.documentElement.style.setProperty(
      "--range-width",
      this.rangeWidth + "px"
    );

    this.rangeBottom.min =
      this.inputBottom.min =
      this.rangeTop.min =
      this.inputTop.min =
      this.bottomBoundary;

    this.rangeBottom.max =
      this.inputBottom.max =
      this.rangeTop.max =
      this.inputTop.max =
      this.topBoundary;

    this.rangeBottom.value =
      this.inputBottom.value = this.bottomValue;

    this.rangeTop.value =
    this.inputTop.value = this.topValue;
    this.updateProgress();
    console.log(this.rangeBottom.value, this.bottomValue)
  }

  // nie wiem jak to inaczej nazwac i w ogole gowniana ta metoda
  eventListenerFactory(elementMin, elementMax, target) {
    console.log('xD')
    let maxValue = +elementMax.value;
    let minValue = +elementMin.value;
    const isMinBiggerThanMax = minValue >= maxValue;
    if (target === elementMin) {
      if (isMinBiggerThanMax) {
        minValue = maxValue - 1;
      }
      if (minValue < this.bottomBoundary) {
        minValue = this.bottomBoundary;
      }
      this.bottomValue = this.inputBottom.value = this.rangeBottom.value = minValue;
    } else {
      if (isMinBiggerThanMax) {
        maxValue = minValue + 1;
      }
      if (maxValue > this.topBoundary) {
        maxValue = this.topBoundary;
      }
      this.topValue = this.inputTop.value = this.rangeTop.value = maxValue;
    }
    this.dispatchEvent(
      new CustomEvent("rangechange", {
        detail: {
          min: this.bottomValue,
          max: this.topValue,
        },
      })
    );
    this.updateProgress();
  }

  setEventListeners() {
    this.rangeTop.addEventListener("input", (e) =>
      this.eventListenerFactory(this.rangeBottom, this.rangeTop, e.target)
    );
    this.inputTop.addEventListener("change", (e) =>
      this.eventListenerFactory(this.inputBottom, this.inputTop, e.target)
    );
    this.rangeBottom.addEventListener("input", (e) =>
      this.eventListenerFactory(this.rangeBottom, this.rangeTop, e.target)
    );
    this.inputBottom.addEventListener("change", (e) =>
      this.eventListenerFactory(this.inputBottom, this.inputTop, e.target)
    );
  }

  updateProgress() {
    const maxValue = this.topValue;
    const minValue = this.bottomValue;
    const minWidth =
      ((minValue - this.bottomBoundary) /
        (this.topBoundary - this.bottomBoundary)) *
      this.rangeWidth;
    const maxWidth =
      ((maxValue - this.bottomBoundary) /
        (this.topBoundary - this.bottomBoundary)) *
      this.rangeWidth;

      console.log(minWidth, maxWidth)
    document.documentElement.style.setProperty(
      "--left",
      Math.floor(minWidth) + "px"
    );
    document.documentElement.style.setProperty(
      "--width",
      Math.floor(maxWidth - minWidth) + "px"
    );
  }
}

customElements.define("price-range", PriceRange);

document
  .querySelector(".range")
  .addEventListener("rangechange", (e) => {
    const url = new URL(location.href)
    const newMin = e.detail.min
    const newMax = e.detail.max
    url.searchParams.set('min', newMin)
    url.searchParams.set('max', newMax)
    // location.href = url.pathname + '?' + url.searchParams.toString()
  });
