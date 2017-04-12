from __future__ import absolute_import
from __future__ import division
import tensorflow as tf
import numpy as np
import csv
import matplotlib.pyplot as plt

class DataSet(object):

  def __init__(self, input, labels, dtype=tf.float32):
    """Construct a DataSet."""

    self._input = input
    self._labels = labels
    self._epochs_completed = 0
    self._index_in_epoch = 0
    self._num_examples = 2509

  @property
  def input(self):
    return self._input

  @property
  def labels(self):
    return self._labels

  @property
  def num_examples(self):
    return self._num_examples

  @property
  def epochs_completed(self):
    return self._epochs_completed

  def next_batch(self, batch_size):
    """Return the next `batch_size` examples from this data set."""
    start = self._index_in_epoch
    self._index_in_epoch += batch_size
    if self._index_in_epoch > self._num_examples:
      # Finished epoch
      self._epochs_completed += 1
      # Shuffle the data
      perm = np.arange(self._num_examples)
      np.random.shuffle(perm)
      self._input = self._input[perm]
      self._labels = self._labels[perm]
      # Start next epoch
      start = 0
      self._index_in_epoch = batch_size
      assert batch_size <= self._num_examples
    end = self._index_in_epoch
    return self._input[start:end], self._labels[start:end]


def read_data_sets(dtype=tf.float32):
    print "Begin Reading Data"
    class DataSets(object):
        pass
    data_sets = DataSets()

    train_input = []
    train_labels = []
    test_input = []
    test_labels = []

    filename_queue = tf.train.string_input_producer(["A.csv"])

    reader = tf.TextLineReader()
    key, value = reader.read(filename_queue)

    default_values = [tf.constant([], dtype=tf.float32),tf.constant([], dtype=tf.float32),tf.constant([], dtype=tf.float32),tf.constant([], dtype=tf.float32),tf.constant([], dtype=tf.float32),tf.constant([], dtype=tf.float32), tf.constant([],dtype=tf.string)]
    volatile, change1, nightchange2, daychange, end1, end2, date = tf.decode_csv(value, record_defaults=default_values)
    features = tf.pack([volatile, change1, nightchange2, end1])
    end2 = tf.pack([end2])

    print "Starting to load train data"

    with tf.Session() as sess:
    # Start populating the filename queue.
        coord = tf.train.Coordinator()
        threads = tf.train.start_queue_runners(coord=coord)

        for i in range(2509):
            # Retrieve a single instance:
            example, label = sess.run([features, end2])
            train_input.append(example)
            train_labels.append(label)

        coord.request_stop()
        coord.join(threads)

    filename_queue = tf.train.string_input_producer(["GOOGL.csv"])

    reader = tf.TextLineReader()
    key, value = reader.read(filename_queue)

    default_values = [tf.constant([], dtype=tf.float32),tf.constant([], dtype=tf.float32),tf.constant([], dtype=tf.float32),tf.constant([], dtype=tf.float32),tf.constant([], dtype=tf.float32),tf.constant([], dtype=tf.float32), tf.constant([],dtype=tf.string)]
    volatile, change1, nightchange2, daychange, end1, end2, date = tf.decode_csv(value, record_defaults=default_values)
    features = tf.pack([volatile, change1, nightchange2, end1])
    end2 = tf.pack([end2])

    print "Starting to load test data"

    with tf.Session() as sess:
    # Start populating the filename queue.
        coord = tf.train.Coordinator()
        threads = tf.train.start_queue_runners(coord=coord)

        for i in range(2509):
        # Retrieve a single instance:
            example, label = sess.run([features, end2])
            test_input.append(example)
            test_labels.append(label)

        coord.request_stop()
        coord.join(threads)

    train_input = np.array(train_input)
    train_labels = np.array(train_labels)
    test_input = np.array(test_input)
    test_labels = np.array(test_labels)

    data_sets.train = DataSet(train_input, train_labels, dtype=dtype)
    data_sets.test = DataSet(test_input, test_labels, dtype=dtype)

    return data_sets

def weight_variable(shape):
  initial = tf.truncated_normal(shape, stddev=0.1)
  return tf.Variable(initial)

def bias_variable(shape):
  initial = tf.constant(0.1, shape=shape)
  return tf.Variable(initial)

stockInfo = read_data_sets()

print "Data Successfully Read In"
print "Starting to learn Neural Network Structure"

x = tf.placeholder(tf.float32, [None, 4])

W1 = weight_variable([4, 500])
b1 = bias_variable([500])

x_1 = tf.nn.relu(tf.matmul(x, W1) + b1)

W2 = weight_variable([500, 250])
b2 = bias_variable([250])

x_2 = tf.nn.relu(tf.matmul(x_1, W2)+b2)

W3 = weight_variable([250, 1])
b3 = bias_variable([1])

y = (tf.matmul(x_2, W3) + b3)
y_ = tf.placeholder(tf.float32, [None, 1])

loss = tf.contrib.losses.squared(y_, y)
train_step = tf.train.AdamOptimizer(0.001).minimize(loss)
init = tf.initialize_all_variables()


print "Teaching the Network"

with tf.Session() as sess:
    sess.run(init)
    for i in range(13):
        batch_xs, batch_ys = stockInfo.train.next_batch(193)
        batch_ys = np.reshape(batch_ys, [-1, 1])

        sess.run(train_step, feed_dict={x:batch_xs, y_: batch_ys})

        print i
        print sess.run(y, feed_dict={x:batch_xs, y_: batch_ys})

    ys = sess.run(y, feed_dict={x:stockInfo.test.input, y_: stockInfo.test.labels})
    print "Done Teaching, Now graphing"
    try:
        fig = plt.figure(figsize=(10,7))
        plt.plot(ys, color='blue')
        plt.plot(stockInfo.test.labels, color='black')
        plt.show()
    except Exception as e:
        print str(e)

    print"Saving the Model"
    saver = tf.train.Saver()
    saver.save(sess, 'saved-model')
    print"Model saved to 'saved-model.meta'"
    sess.close()

#all further code is used to load the saved model and run the testing data again
# print"Loading saved model and starting new session"
# with tf.Session() as sess:
#     sess.run(tf.initialize_all_variables())
#     saver.restore(sess, tf.train.latest_checkpoint('./'))
#     print("Model restored running testing data again")
#     sess.run(accuracy, feed_dict={x: stockInfo.train.input, y_: stockInfo.train.labels})
#     correct_prediction = tf.equal(tf.argmax(y,1), tf.argmax(y_,1))
#     accuracy = tf.reduce_mean(tf.cast(correct_prediction, tf.float32))
#     print "Test Accuracy round 2"
#     print sess.run(accuracy, feed_dict={x: stockInfo.test.input, y_: stockInfo.test.labels})
