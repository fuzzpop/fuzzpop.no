'use strict';

// Keep column sizing behavior aligned with Pekosoft's setupResizableTables.
const resizableTableControllers = new WeakMap();

function resetResizableTables() {
  document.querySelectorAll('table.resizable-columns').forEach((table) => {
    resizableTableControllers.get(table)?.reset();
  });
}

window.PekoTables = {
  reset: resetResizableTables
};

function setupResizableTables() {
  document.querySelectorAll('table').forEach((table) => {
    if (table.dataset.resizableColumns === 'true') return;
    if (!table.tHead || !table.tHead.querySelector('th')) return;

    const getVisibleHeaders = () => [...table.querySelectorAll('thead th')]
      .filter((header) => getComputedStyle(header).display !== 'none');
    let resizingHeader = null;
    let startX = 0;
    let startWidth = 0;
    let startTableWidth = 0;
    let lastTouchResizeTap = null;
    let resizeMoved = false;
    let defaultsAreFinalized = false;
    let suppressSortClick = false;

    const getResizeEdgeWidth = (event) => event.pointerType === 'touch' ? 24 : 8;
    const getMinimumTableWidth = () => table.parentElement?.clientWidth || 0;
    const rebaseColumnWidths = (headers, updateDefaults = false) => {
      const widths = headers.map((header) => ({
        header,
        width: Math.round(header.getBoundingClientRect().width)
      }));

      widths.forEach(({ header, width }) => {
        if (width <= 0) return;
        header.dataset.columnWidth = String(width);
        if (updateDefaults) header.dataset.defaultColumnWidth = String(width);
        header.style.width = `${width}px`;
      });

      const totalWidth = widths.reduce((width, column) => width + column.width, 0);
      if (totalWidth > 0) table.style.width = `${Math.max(totalWidth, getMinimumTableWidth())}px`;
    };

    const syncColumnWidths = () => {
      if (resizingHeader || table.classList.contains('table-cards')) return;

      const headers = getVisibleHeaders();
      if (!headers.length) return;

      headers.forEach((header) => {
        const width = Math.round(header.getBoundingClientRect().width);
        if (width > 0 && !header.dataset.defaultColumnWidth) {
          header.dataset.defaultColumnWidth = String(width);
        }
        if (width > 0 && !header.dataset.columnWidth) {
          header.dataset.columnWidth = String(width);
          header.style.width = `${width}px`;
        }
      });

      const totalWidth = headers.reduce((width, header) => {
        return width + Number(header.dataset.columnWidth || 0);
      }, 0);
      const minimumTableWidth = getMinimumTableWidth();
      if (totalWidth < minimumTableWidth) {
        rebaseColumnWidths(headers);
      }

      const syncedWidth = headers.reduce((width, header) => {
        return width + Number(header.dataset.columnWidth || 0);
      }, 0);
      if (syncedWidth > 0) table.style.width = `${Math.max(syncedWidth, minimumTableWidth)}px`;
    };

    const finishResize = () => {
      if (!resizingHeader) return;
      setResizingColumn(resizingHeader, false);
      resizingHeader = null;
    };

    const setResizingColumn = (header, isResizing) => {
      const columnIndex = getVisibleHeaders().indexOf(header);
      if (columnIndex < 0) return;

      [...table.rows].forEach((row) => {
        if (row.parentElement?.tagName === 'TFOOT') return;
        row.cells[columnIndex]?.classList.toggle('column-resizing', isResizing);
      });
    };

    const reset = () => {
      finishResize();
      table.classList.remove('resizable-columns');
      table.style.removeProperty('width');
      table.querySelectorAll('thead th').forEach((header) => {
        header.style.removeProperty('width');
        delete header.dataset.columnWidth;
        delete header.dataset.defaultColumnWidth;
        header.classList.remove('column-resize-target', 'column-resizing');
      });
      table.querySelectorAll('td.column-resizing').forEach((cell) => {
        cell.classList.remove('column-resizing');
      });
      syncColumnWidths();
      table.classList.add('resizable-columns');
    };

    const resetColumnWidth = (header) => {
      const defaultWidth = Number(header.dataset.defaultColumnWidth);
      if (!Number.isFinite(defaultWidth) || defaultWidth <= 0) return;

      const currentWidth = header.getBoundingClientRect().width;
      header.dataset.columnWidth = String(defaultWidth);
      header.style.width = `${defaultWidth}px`;
      table.style.width = `${Math.max(getMinimumTableWidth(), Math.round(table.getBoundingClientRect().width + defaultWidth - currentWidth))}px`;
    };

    table.addEventListener('pointermove', (event) => {
      if (resizingHeader) {
        if (Math.abs(event.clientX - startX) > 3) resizeMoved = true;
        const width = Math.max(48, startWidth + event.clientX - startX);
        const widthChange = width - startWidth;
        resizingHeader.dataset.columnWidth = String(Math.round(width));
        resizingHeader.style.width = `${Math.round(width)}px`;
        table.style.width = `${Math.max(getMinimumTableWidth(), Math.round(startTableWidth + widthChange))}px`;
        return;
      }

      const header = event.target.closest('th');
      table.querySelectorAll('.column-resize-target').forEach((target) => {
        target.classList.remove('column-resize-target');
      });
      if (!header || !table.tHead?.contains(header)) return;

      const bounds = header.getBoundingClientRect();
      if (event.clientX >= bounds.right - getResizeEdgeWidth(event)) {
        header.classList.add('column-resize-target');
      }
    });

    table.addEventListener('pointerleave', () => {
      if (resizingHeader) return;
      table.querySelectorAll('.column-resize-target').forEach((header) => {
        header.classList.remove('column-resize-target');
      });
    });

    table.addEventListener('pointerdown', (event) => {
      if (event.button !== 0) return;

      const header = event.target.closest('th');
      if (!header || !table.tHead?.contains(header)) return;

      let bounds = header.getBoundingClientRect();
      if (event.clientX < bounds.right - getResizeEdgeWidth(event)) return;

      const headers = getVisibleHeaders();
      const storedWidth = headers.reduce((width, visibleHeader) => {
        return width + Number(visibleHeader.dataset.columnWidth || 0);
      }, 0);
      if (storedWidth < getMinimumTableWidth()) {
        rebaseColumnWidths(headers, !defaultsAreFinalized);
        defaultsAreFinalized = true;
        bounds = header.getBoundingClientRect();
      }

      event.preventDefault();
      resizingHeader = header;
      startX = event.clientX;
      startWidth = bounds.width;
      startTableWidth = table.getBoundingClientRect().width;
      resizeMoved = false;
      setResizingColumn(header, true);
      header.setPointerCapture(event.pointerId);
    });

    table.addEventListener('pointerup', (event) => {
      const header = resizingHeader;
      if (header) {
        suppressSortClick = true;
        setTimeout(() => { suppressSortClick = false; }, 0);
      }
      finishResize();
      if (!header || event.pointerType !== 'touch') return;

      if (resizeMoved) {
        lastTouchResizeTap = null;
        return;
      }

      const now = event.timeStamp;
      const isDoubleTap = lastTouchResizeTap?.header === header && now - lastTouchResizeTap.time < 350;
      lastTouchResizeTap = { header, time: now };
      if (isDoubleTap) {
        resetColumnWidth(header);
        lastTouchResizeTap = null;
      }
    });
    table.addEventListener('pointercancel', finishResize);
    table.addEventListener('lostpointercapture', finishResize);
    table.addEventListener('tableviewchange', () => {
      finishResize();
      syncColumnWidths();
    });

    // Sorttable listens on each header; resize gestures must not sort rows.
    table.addEventListener('click', (event) => {
      const header = event.target.closest('th');
      if (!header || !table.tHead?.contains(header)) return;
      if (suppressSortClick || (event.detail > 0 && event.clientX >= header.getBoundingClientRect().right - getResizeEdgeWidth(event))) {
        event.stopImmediatePropagation();
      }
    }, true);

    table.addEventListener('dblclick', (event) => {
      const header = event.target.closest('th');
      if (!header || !table.tHead?.contains(header)) return;

      const bounds = header.getBoundingClientRect();
      if (event.clientX < bounds.right - getResizeEdgeWidth(event)) return;

      event.preventDefault();
      resetColumnWidth(header);
    });

    table.dataset.resizableColumns = 'true';
    syncColumnWidths();
    table.classList.add('resizable-columns');
    resizableTableControllers.set(table, { reset });

    window.requestAnimationFrame(() => {
      if (table.classList.contains('table-cards')) return;
      const headers = getVisibleHeaders();
      const storedWidth = headers.reduce((width, header) => {
        return width + Number(header.dataset.columnWidth || 0);
      }, 0);
      if (storedWidth < getMinimumTableWidth()) rebaseColumnWidths(headers, true);
      defaultsAreFinalized = true;
    });

    const observer = new MutationObserver(() => {
      window.requestAnimationFrame(syncColumnWidths);
    });
    observer.observe(table.tHead, {
      attributes: true,
      attributeFilter: ['style'],
      childList: true,
      subtree: true
    });
  });
}

document.addEventListener('DOMContentLoaded', setupResizableTables);
