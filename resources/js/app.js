import './bootstrap';
import DataTable from 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-datatable]').forEach((table) => {
        const searchUrl = table.dataset.source;
        if (!searchUrl) {
            return;
        }

        new DataTable(table, {
            processing: true,
            serverSide: true,
            ajax: searchUrl,
            pageLength: 10,
            lengthMenu: [10, 25, 50],
            order: [[0, 'asc']],
            columns: [...table.querySelectorAll('thead th')].map((header) => ({
                data: header.dataset.column,
                render: DataTable.render.text(),
                orderable: header.dataset.sortable !== 'false',
            })),
            language: {
                emptyTable: 'No records to display.',
                processing: 'Loading records…',
                search: 'Search:',
            },
        });
    });

    document.querySelectorAll('[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });
});
