import React, {useEffect} from 'react';
import PropTypes from 'prop-types';

//TODO extract css to sass
import './Modal.css';

export const Type = ['confirm', 'confirmNavigate', 'warning'];

const ModalWrapper = ({message, onDismiss, type = 'confirm'}) => {
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
            } else {
                modalInstance = modal[type]( message, {yes: okay, no: cancel});
            }
        }
    }

    useEffect(() => {
        import('TYPO3/CMS/FrontendEditing/Modal').then(({default: Modal}) => {
            modal = Modal;
            show();
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
    title: PropTypes.string,
    message: PropTypes.string,
};


