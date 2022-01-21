import React, { useState, useEffect } from 'react';
import { useDispatch } from 'react-redux';
import { addSlot, removeSlot, editSlot, editDay } from '../../features/branchesSlice';
import Slot from './Slot';

function ScheduleDay({ branchId, name, slots, preparationTime }) {
  const dispatch = useDispatch();
  const [daySlots, setDaySlots] = useState([...slots]);
  const [preparation, setPreparation] = useState(preparationTime);

  useEffect(() => {
    setDaySlots([...slots]);
  }, [slots]);


  const handleAddSlot = e => {
    e.preventDefault();

    dispatch(addSlot({
      branchId,
      day: name
    }))
  }

  const handleRemoveSlot = (index) => {
    dispatch(removeSlot({
      branchId,
      day: name,
      index
    }))
  }

  const handleSlotChange = (slot, index) => {
    dispatch(editSlot({
      branchId,
      day: name,
      index,
      slot
    }))
  }

  const handlePreparationBlur = () => {
    dispatch(editDay({
      branchId,
      day: name,
      data: {
        preparationTime: preparation
      }
    }))
  }

  return (
    <div className='sp-schedule-day'>
      <h4 className='sp-schedule-day__title'>{name}</h4>
      {daySlots.length > 0 && daySlots.map((slot, index) => (
        <Slot
          key={index}
          index={index}
          from={slot[0]}
          to={slot[1]}
          onChange={(slot) => handleSlotChange(slot, index)}
          onRemove={() => handleRemoveSlot(index)} />
      ))}
      <button className='button button-primary' onClick={handleAddSlot}>Add</button>
      <input name='preparation-time' onBlur={handlePreparationBlur} onChange={e => setPreparation(e.currentTarget.value)} autoComplete="new-password" value={preparation} className="preparation-time" type="number" style={{ display: "block", margin: "10px auto 0" }} placeholder="Preparation time" />
    </div>
  );
}

ScheduleDay.defaultProps = {
  name: '',
  slots: [],
  preparationTime: ''
}

export default ScheduleDay;
