import React, { useState } from 'react';
import TimePicker from './TimePicker';

function Slot({ from, to, onRemove, onChange }) {
  const [fromTime, setFromTime] = useState(from);
  const [toTime, setToTime] = useState(to);

  const handleRemoveSlot = (e) => {
    e.preventDefault();
    if (onRemove) onRemove();
  }

  const handleBlur = () => {
    onChange([fromTime, toTime]);
  };

  return (
    <li className='sp-schedule-day__slot'>
      <TimePicker name='slot-from' onBlur={handleBlur} onChange={e => setFromTime(e.currentTarget.value)} placeholder="From" autoComplete="new-password" required value={fromTime} />
      <TimePicker name='slot-to' onBlur={handleBlur} onChange={e => setToTime(e.currentTarget.value)} placeholder="To" autoComplete="new-password" required value={toTime} />
      <a href="#" className="remove-slot" onClick={handleRemoveSlot}>
        <i className="gg-trash"></i>
      </a>
    </li>
  );
}

Slot.defaultProps = {
  from: '',
  to: '',
}

export default Slot;
