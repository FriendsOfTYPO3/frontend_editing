import React, {useEffect} from 'react';
import PropTypes from 'prop-types';

//TODO extract css to sass
import './Modal.css';

export const Type = ['confirm', 'confirmNavigate', 'warning'];

const ModalWrapper = ({message, onDismiss, onError, type = 'confirm'}) => {
    let modal = null;
    let modalInstance = null;

    function okay () {
        onDismiss('okay');
        modalInstance = null;
    }

    function save () {
        onDismiss('save');
        modalInstance = null;
    }

    function cancel () {
        onDismiss('cancel');
        modalInstance = null;
    }

    function show () {
        if (modal) {
            if (type === 'confirmNavigate') {
                modalInstance = modal[type]( message, save, {yes: okay, no: cancel});
            } else if (type === 'variable_not_defined') {
                modalInstance = modal.builder.modal();
            } else if (type === 'variable_not_function') {
                modalInstance = modal.builder.modal('title', 'content')
                    .onReady('str');
            } else if (type === 'variable_not_integer') {
                modalInstance = modal.builder.modal('title', 'content')
                    .setSeverity('1');
            } else {
                modalInstance = modal[type]( message, {yes: okay, no: cancel});
            }
        }
    }

    useEffect(() => {
        import('TYPO3/CMS/FrontendEditing/Modal').then(({default: Modal}) => {
            try {
                modal = Modal;
                show();
            } catch (exception) {
                onError(exception.toString());
            }
        });
        return () => {
            if (modalInstance) {
                modalInstance.trigger('modal-dismiss');
            }
        };
    });

    return (
        <div style={{
            display: 'flex',
            padding: '16px',
            gap: '8px',
            background: 'lightgray',
            borderBottom: '1px solid gray',
        }}>
            <button onClick={show}>show</button>
        </div>
    );
};

export default ModalWrapper;

ModalWrapper.propTypes = {
    message: PropTypes.string,
};


