import React from 'react';
import { v4 as uuid } from 'uuid';

function TimePicker({ selected, ...props }) {
  const listid = React.useRef(uuid());
  const [timeArray, setTimeArray] = React.useState([]);

  const getTimeList = React.useCallback(() => {
    const res = [];

    for (let index = 0; index < 24; index++) {
      let hour = `${index}`.length < 2 ? `0${index}` : index;
      res.push(`${hour}:00`);
    }

    return res;
  }, []);

  React.useEffect(() => {
    setTimeArray(getTimeList());
  }, []);

  return (
    <select {...props}>
      {timeArray.length > 0 && timeArray.map((time, index) => (
        <option key={index} value={time}>{time}</option>
      ))}
    </select>
  );
}

export default TimePicker;
