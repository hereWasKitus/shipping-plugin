import React from 'react';
import ScheduleDay from './ScheduleDay';

function Schedule({ schedule, branchId }) {
  const days = React.useRef(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']);

  return (
    <div className="sp-schedule">
      {days.current.map((day, index) => (
        <ScheduleDay
          key={index}
          name={day}
          slots={schedule[day].slots}
          branchId={branchId}
          preparationTime={schedule[day].preparationTime} />
      ))}
    </div>
  );
}

export default Schedule;
