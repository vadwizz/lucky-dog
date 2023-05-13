document.addEventListener(
    "DOMContentLoaded", () => {
        new Mmenu( "#mmenu", {
          theme: "light",
          offCanvas	: {
			position: "right-front"
		  },
		  counters: {
			add: true,
		  },
		  navbar: {
			title: "PortaPia"
		  },
		  navbars: [true],
        });
    }
);


