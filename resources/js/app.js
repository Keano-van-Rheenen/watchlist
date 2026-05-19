import Sortable from 'sortablejs';

const container = document.querySelector('[data-watchlist-sortable]');

if (container) {
	const reorderUrl = container.dataset.reorderUrl;
	const csrfToken = container.dataset.csrfToken;
	const statusElement = document.querySelector('[data-watchlist-reorder-status]');

	let saveController = null;
	let previousOrder = [];

	const cardElements = () => Array.from(container.querySelectorAll('[data-watchable-id]'));

	const currentOrder = () => cardElements().map((card) => card.dataset.watchableId);

	const updateRanks = () => {
		cardElements().forEach((card, index) => {
			const rank = card.querySelector('[data-watchable-rank]');

			if (rank) {
				rank.textContent = String(index + 1);
			}
		});
	};

	const setStatus = (message, type = 'success') => {
		if (!statusElement) {
			return;
		}

		statusElement.textContent = message;
		statusElement.style.color = type === 'error' ? '#b91c1c' : '#0f766e';
	};

	const restoreOrder = (orderedIds) => {
		const byId = new Map(cardElements().map((card) => [card.dataset.watchableId, card]));

		orderedIds.forEach((id) => {
			const card = byId.get(id);

			if (card) {
				container.appendChild(card);
			}
		});

		updateRanks();
	};

	updateRanks();

	new Sortable(container, {
		animation: 180,
		easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
		handle: '[data-drag-handle]',
		draggable: '[data-watchable-id]',
		ghostClass: 'watchable-card-ghost',
		chosenClass: 'watchable-card-chosen',
		onMove: (evt) => {
			const draggedRect = evt.dragged.getBoundingClientRect();
			const edgeMargin = 150;

			if (draggedRect.bottom > window.innerHeight - edgeMargin) {
				window.scrollBy(0, 40);
			} else if (draggedRect.top < edgeMargin) {
				window.scrollBy(0, -40);
			}
		},
		onStart: () => {
			previousOrder = currentOrder();
			setStatus('Reordering...');
		},
		onEnd: async () => {
			updateRanks();

			const orderedIds = currentOrder();

			if (orderedIds.join(',') === previousOrder.join(',')) {
				setStatus('');

				return;
			}

			if (saveController) {
				saveController.abort();
			}

			saveController = new AbortController();

			try {
				const response = await fetch(reorderUrl, {
					method: 'PATCH',
					headers: {
						'Content-Type': 'application/json',
						'Accept': 'application/json',
						'X-CSRF-TOKEN': csrfToken,
					},
					credentials: 'same-origin',
					body: JSON.stringify({ ordered_ids: orderedIds }),
					signal: saveController.signal,
				});

				if (!response.ok) {
					throw new Error('Could not save the new order.');
				}

				setStatus('Order saved.');
			} catch (error) {
				if (error?.name === 'AbortError') {
					return;
				}

				restoreOrder(previousOrder);
				setStatus('Could not save order. Please try again.', 'error');
			}
		},
	});
}
